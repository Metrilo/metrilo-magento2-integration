<?php

namespace Metrilo\Analytics\Helper;

/**
 * helper class
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const DATA_TAG = "metrilo_events";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->config = $scopeConfig;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * Check if metrilo module is enabled
     *
     * @return boolean
     */
    public function isEnabled() {
        return $this->config->getValue('analytics/general/enable',
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE
               );
    }

    /**
     * Get API Token from system configuration
     *
     * @return string
     */
    public function getApiToken() {
        return $this->config->getValue('analytics/general/api_key',
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE
               );
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiSecret() {
        return $this->config->getValue('analytics/general/api_secret',
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE
               );
    }

    /**
     * Get session data with "metrilo_events" key
     *
     * @return array
     */
    public function getSessionEvents() {
        $events = [];
        if($this->session->getData(self::DATA_TAG)) {
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
    public function addSessionEvent($method, $type, $data, $metaData = false) {
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
    public function logError($exception) {
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
        $data = array(
            'order_id'          => $order->getIncrementId(),
            'cart_id'           => $order->getQuoteId(),
            'order_status'      => $order->getStatus(),
            'amount'            => (float)$order->getGrandTotal(),
            'shipping_amount'   => (float)$order->getShippingAmount(),
            'tax_amount'        => $order->getTaxAmount(),
            'items'             => array(),
            'shipping_method'   => $order->getShippingDescription(),
            'payment_method'    => $order->getPayment()->getMethodInstance()->getTitle(),
        );
        if ($order->getCouponCode()) {
            $data['coupons'] = array($order->getCouponCode());
        }
        $skusAdded = array();
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getSku(), $skusAdded)) continue;
            $skusAdded[] = $item->getSku();
            $dataItem = array(
                'id'        => $item->getProductId(),
                'price'     => (float)$item->getPrice() ? $item->getPrice() : $item->getProduct()->getFinalPrice(),
                'name'      => $item->getName(),
                'url'       => $item->getProduct()->getProductUrl(),
                'quantity'  => (int)$item->getQtyOrdered()
            );
            /*if ($item->getProductType() == 'configurable' || $item->getProductType() == 'grouped') {
                if ($item->getProductType() == 'grouped') {
                    // $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($item->getProductId());
                    $parentId = $parentIds[0];
                } else {
                    $parentId = $item->getProductId();
                }
                $mainProduct = Mage::getModel('catalog/product')->load($parentId);
                $dataItem['id']     = $mainProduct->getId();
                $dataItem['name']   = $mainProduct->getName();
                $dataItem['url']    = $mainProduct->getProductUrl();
                $dataItem['option_id'] = $item->getSku();
                $dataItem['option_name'] = trim(str_replace("-", " ", $item->getName()));
                $dataItem['option_price'] = (float)$item->getPrice();
            }*/
            $data['items'][] = $dataItem;
        }
        return $data;
    }

    /**
     * Create HTTP request to metrilo server
     *
     * @param  string  $ident
     * @param  string  $event
     * @param  array  $params
     * @param  boolean|array $identityData
     * @param  boolean|int $time
     * @param  boolean|array $callParameters
     * @return void
     */
    public function callApi($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
        try {
            $call = $this->buildEventArray($ident, $event, $params, $identityData, $time, $callParameters);
            // We should handle the setting of token parameter, as it's part of the request
            $call['token'] = $this->getApiToken();
            // Additional ksort here because of adding token param
            ksort($call);
            $basedCall = base64_encode(Mage::helper('core')->jsonEncode($call));
            $signature = md5($basedCall.$this->getApiSecret());
            // Use Varien_Http_Client
            // to generate API call end point and call it
            $url = 'http://p.metrilo.com/t?s='.$signature.'&hs='.$basedCall;
            $client = new Varien_Http_Client($url);
            $response = $client->request();
            $result = Mage::helper('core')->jsonDecode($response->getBody());
            if (!$result['status']) {
                Mage::log($result['error'], null, 'Metrilo_Analytics.log');
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'Metrilo_Analytics.log');
        }
    }

    /**
     * [callBatchApi description]
     * @param  [type] $ordersForSubmition [description]
     * @return [type]                     [description]
     */
    public function callBatchApi($ordersForSubmition)
    {
        try {
            // Consider token is in the first level in the hashed json
            $version = (string)Mage::getConfig()->getModuleConfig("Metrilo_Analytics")->version;
            $call = array(
                'token'    => $this->getApiToken(),
                'events'   => $ordersForSubmition,
                // for debugging/support purposes
                'platform' => 'Magento ' . Mage::getEdition() . ' ' . Mage::getVersion(),
                'version'  => $version
            );
            // Additional ksort here because of adding token param
            ksort($call);
            $basedCall = base64_encode(Mage::helper('core')->jsonEncode($call));
            $signature = md5($basedCall.$this->getApiSecret());
            $url = 'http://p.metrilo.com/bt';
            $client = new Varien_Http_Client($url);
            $requestBody = array(
                's'   => $signature,
                'hs'  => $basedCall
            );
            // This method supports passing named array as well as key, value
            $client->setParameterPost($requestBody);
            $response = $client->request('POST');
            if ($response->isError()) {
                Mage::log($response->getBody(), null, 'Metrilo_Analytics.log');
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'Metrilo_Analytics.log');
        }
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
     * @return void
     */
    public function buildEventArray($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
      $call = array(
          'event_type'    => $event,
          'params'        => $params,
          'uid'           => $ident
      );
      if($time) {
          $call['time'] = $time;
      }
      // check for special parameters to include in the API call
      if($callParameters) {
          if($callParameters['use_ip']) {
              $call['use_ip'] = $callParameters['use_ip'];
          }
      }
      // put identity data in call if available
      if($identityData) {
          $call['identity'] = $identityData;
      }
      // Prepare keys is alphabetical order
      ksort($call);
      return $call;
    }
}
