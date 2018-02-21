<?php

namespace Metrilo\Analytics\Helper;

/**
 * Helper class
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const DATA_TAG = 'metrilo_events';

    const MODULE_NAME = 'Metrilo_Analytics';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Customer\Model\Session                    $session
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Catalog\Model\ProductRepository           $productRepository
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param Async                                              $asyncHelper
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\ProductMetadata             $metaData
     * @param \Magento\Framework\Module\ModuleListInterface      $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Async $asyncHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadata $metaData,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->jsonHelper = $jsonHelper;
        $this->asyncHelper = $asyncHelper;
        $this->storeManager = $storeManager;
        $this->metaData = $metaData;
        $this->moduleList = $moduleList;
    }

    /**
     * Check if metrilo module is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->config->getValue(
            'metrilo_analytics/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API Token from system configuration
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiSecret()
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get session data with "metrilo_events" key
     *
     * @return array
     */
    public function getSessionEvents()
    {
        $events = [];
        if ($this->session->getData(self::DATA_TAG)) {
            $events = $this->session->getData(self::DATA_TAG, true);
        }
        return $events;
    }

    /**
     * Add event to session
     *
     * @param string  $method
     * @param string  $type
     * @param array   $data
     * @param boolean|string $metaData
     */
    public function addSessionEvent($method, $type, $data, $metaData = false)
    {
        $events = [];
        if ($this->session->getData(self::DATA_TAG) != '') {
            $events = (array)$this->session->getData(self::DATA_TAG);
        }
        $eventToAdd = array(
            'method' => $method,
            'type' => $type,
            'data' => $data
        );
        if ($metaData) {
            $eventToAdd['metaData'] = $metaData;
        }
        array_push($events, $eventToAdd);
        $this->session->setData(self::DATA_TAG, $events);
    }

    /**
     * Log error to logs
     *
     * @param  \Exception $exception
     * @return void
     */
    public function logError($exception)
    {
        if ($exception instanceof \Exception) {
            $this->logger->critical($exception->getMessage());
        } else {
            $this->logger->critical($exception);
        }
    }

    /**
     * Get order details and sort them for metrilo
     *
     * @param  Mage_Sales_Model_Order $order
     * @return array
     */
    public function prepareOrderDetails($order)
    {
        $data = [
            'order_id'          => $order->getIncrementId(),
            'quote_id'           => $order->getQuoteId(),
            'order_status'      => $order->getStatus(),
            'amount'            => (float)$order->getGrandTotal(),
            'shipping_amount'   => (float)$order->getShippingAmount(),
            'tax_amount'        => $order->getTaxAmount(),
            'items'             => [],
            'shipping_method'   => $order->getShippingDescription(),
            'payment_method'    => $order->getPayment()->getMethodInstance()->getTitle(),
        ];

        $this->_assignBillingInfo($data, $order);

        if ($order->getCouponCode()) {
            $data['coupons'] = array($order->getCouponCode());
        }
        $skusAdded = array();
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getSku(), $skusAdded)) {
                continue;
            }
            $skusAdded[] = $item->getSku();
            $dataItem = array(
                'id'        => (int)$item->getProductId(),
                'price'     => (float)$item->getPrice() ? $item->getPrice() : $item->getProduct()->getFinalPrice(),
                'name'      => $item->getName(),
                'url'       => $item->getProduct()->getProductUrl(),
                'quantity'  => (int)$item->getQtyOrdered()
            );
            if ($item->getProductType() == 'configurable') {
                $parentId = $item->getProductId();
                $mainProduct = $this->productRepository->getById($parentId);
                $options = (array)$item->getProductOptions();
                $dataItem['option_id'] = $options['simple_sku'];
                $dataItem['option_name'] = $options['simple_name'];
                $dataItem['option_price'] = (float)$item->getPrice();
            }
            $data['items'][] = $dataItem;
        }
        return $data;
    }

    /**
     * API call to Metrilo to submit information asynchronously
     *
     * @param  int $storeId
     * @param  array $orders
     * @return void
     */
    public function callBatchApi($storeId, $orders)
    {
        $ordersForSubmission = $this->_buildOrdersForSubmission($orders);
        $call = $this->_buildCall($storeId, $ordersForSubmission);
        $this->_callMetriloApi($storeId, $call);
    }

    /**
     * Submit orders to Metrilo API via post request
     *
     * @param  int $storeId
     * @param  array $call
     * @return void
     */
    protected function _callMetriloApi($storeId, $call)
    {
        ksort($call);
        $basedCall = base64_encode($this->jsonHelper->jsonEncode($call));
        $signature = md5($basedCall . $this->getApiSecret($storeId));
        $requestBody = [
            's'   => $signature,
            'hs'  => $basedCall
        ];
        $this->asyncHelper->post('http://p.metrilo.com/bt', $requestBody);
    }

    /**
     * Create submition ready arrays from Array of \Magento\Sales\Model\Order
     *
     * @param \Magento\Sales\Model\Order[] $orders
     * @return array
     */
    protected function _buildOrdersForSubmission($orders)
    {
        $ordersForSubmission = [];
        foreach ($orders as $order) {
            if ($order->getId()) {
                array_push($ordersForSubmission, $this->_buildOrderForSubmission($order));
            }
        }
        return $ordersForSubmission;
    }

    /**
     * Build individual order data
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _buildOrderForSubmission($order)
    {
        $orderDetails = $this->prepareOrderDetails($order);
        // initialize additional params
        $callParameters = [
            'server_time' => round(microtime(true) * 1000)
        ];
        // check if order has customer IP in it
        $ip = $order->getRemoteIp();
        if ($ip) {
            $callParameters['use_ip'] = $ip;
        }
        // initialize time
        $time = false;
        if ($order->getCreatedAt()) {
            $dateObj = new \DateTime($order->getCreatedAt());
            $time = $dateObj->getTimestamp() * 1000;
        }
        $identityData = $this->_orderIdentityData($order);
        return $this->_buildEventArray(
            $identityData['email'],
            'order',
            $orderDetails,
            $identityData,
            $time,
            $callParameters
        );
    }

    /**
     * Get Order Customer identity data
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _orderIdentityData($order)
    {
        return array(
            'email'         => $order->getCustomerEmail(),
            'first_name'    => $order->getBillingAddress()->getFirstname(),
            'last_name'     => $order->getBillingAddress()->getLastname(),
            'name'          => $order->getBillingAddress()->getName(),
        );
    }

    /**
     * Create call array
     *
     * @param  int $storeId
     * @param  array $ordersForSubmission
     * @return array
     */
    protected function _buildCall($storeId, $ordersForSubmission)
    {
        return array(
            'token'    => $this->getApiToken($storeId),
            'events'   => $ordersForSubmission,
            // for debugging/support purposes
            'platform' => 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion(),
            'version'  => $this->moduleList->getOne(self::MODULE_NAME)['setup_version']
        );
    }

    /**
     * Assign billing information
     *
     * @param  array $data
     * @param  \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function _assignBillingInfo(&$data, $order)
    {
        $billingAddress = $order->getBillingAddress();
        // Assign billing data to order data array
        $data['billing_phone'] = $billingAddress->getTelephone();
        $data['billing_country'] = $billingAddress->getCountryId();
        $data['billing_region'] = $billingAddress->getRegion();
        $data['billing_city'] = $billingAddress->getCity();
        $data['billing_postcode'] = $billingAddress->getPostcode();
        $data['billing_address'] = ''; // Populate below
        $data['billing_company'] = $billingAddress->getCompany();
        $street = $billingAddress->getStreet();
        $data['billing_address'] = is_array($street) ? implode(PHP_EOL, $street) : $street;
    }

    /**
     * Build event array ready for encoding and encrypting. Built array is returned using ksort.
     *
     * @param  string  $ident
     * @param  string  $event
     * @param  array  $params
     * @param  boolean|array $identityData
     * @param  boolean|int $time
     * @param  boolean|array $callParameters
     * @return array
     */
    protected function _buildEventArray($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
        $call = array(
            'event_type'    => $event,
            'params'        => $params,
            'uid'           => $ident
        );
        if ($time) {
            $call['time'] = $time;
        }
        // check for special parameters to include in the API call
        if ($callParameters && isset($callParameters['use_ip'])) {
            $call['use_ip'] = $callParameters['use_ip'];
        }
        // put identity data in call if available
        if ($identityData) {
            $call['identity'] = $identityData;
        }
        // Prepare keys is alphabetical order
        ksort($call);
        return $call;
    }

    /**
     * Get storeId for the current request context
     *
     * @param null $request
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
