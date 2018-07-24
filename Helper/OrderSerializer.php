<?php

namespace Metrilo\Analytics\Helper;

class OrderSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Metrilo\Analytics\Helper\ImagePathResolver $imagePathResolver
    ) {
        $this->productRepository = $productRepository;
        $this->imagePathResolver = $imagePathResolver;
    }

    /**
     * Build individual order data
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    public function buildOrderForSubmission($order)
    {
        $identityData = $this->orderIdentityData($order);

        $call = array(
            'event_type'  => 'order',
            'params'      => $this->prepareOrderDetails($order),
            'uid'         => $identityData['email'],
            'identity'    => $identityData,
            'server_time' => round(microtime(true) * 1000),
        );

        // check if order has customer IP in it
        $ip = $order->getRemoteIp();
        if ($ip) {
            $call['use_ip'] = $ip;
        }
        // initialize time
        if ($order->getCreatedAt()) {
            $dateObj = new \DateTime($order->getCreatedAt());
            $call['time'] = $dateObj->getTimestamp() * 1000;
        }

        ksort($call);
        return $call;
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
            'order_status'      => $order->getStatus(),
            'amount'            => (float)$order->getBaseGrandTotal(),
            'shipping_amount'   => (float)$order->getBaseShippingAmount(),
            'tax_amount'        => $order->getBaseTaxAmount(),
            'items'             => [],
            'shipping_method'   => $order->getShippingDescription(),
            'payment_method'    => $order->getPayment()->getMethodInstance()->getTitle(),
        ];

        $this->assignBillingInfo($data, $order);

        if ($order->getCouponCode()) {
            $data['coupons'] = array($order->getCouponCode());
        }
        $skusAdded = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $data['items'][] = $this->getProductDetails($item);
        }
        return $data;
    }

    private function getProductDetails($quoteItem)
    {
        $dataItem = array(
            'id'        => (int)$quoteItem->getProductId(),
            'price'     => (float)$quoteItem->getBasePrice(),
            'name'      => $quoteItem->getName(),
            'quantity'  => (int)$quoteItem->getQtyOrdered()
        );

        if ($quoteItem->getProductType() == 'configurable') {
            $options = (array)$quoteItem->getProductOptions();
            $dataItem['option_id'] = $options['simple_sku'];
            // for legacy reasons - we have been passing the SKU as ID for the child products
            $dataItem['option_sku'] = $options['simple_sku'];
            $dataItem['option_name'] = $options['simple_name'];
            $dataItem['option_price'] = (float)$quoteItem->getBasePrice();
        }

        try {
            if ($quoteItem->getProductType() == 'configurable') {
                $parentId = $quoteItem->getProductId();
                $product = $this->productRepository->getById($parentId);
            } else {
                $product = $quoteItem->getProduct();
            }

            if ($product) {
                $imageBasePath = $this->imagePathResolver->getBaseImage($product);
                if(!empty($imageBasePath)) {
                    $dataItem['image_url'] = $imageBasePath;
                }
                $dataItem['url'] = $product->getProductUrl();
                $dataItem['sku'] = $product->getSku();
            }

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {}

        return $dataItem;
    }

    /**
     * Get Order Customer identity data
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return array
     */
    private function orderIdentityData($order)
    {
        return array(
            'email'         => $order->getCustomerEmail(),
            'first_name'    => $order->getBillingAddress()->getFirstname(),
            'last_name'     => $order->getBillingAddress()->getLastname(),
            'name'          => $order->getBillingAddress()->getName(),
        );
    }

    /**
     * Assign billing information
     *
     * @param  array $data
     * @param  \Magento\Sales\Model\Order $order
     * @return void
     */
    private function assignBillingInfo(&$data, $order)
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
    private function buildEventArray($ident, $event, $params, $identityData = false, $time = false, $extraParameters)
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
        if ($extraParameters) {
            $call = array_merge($call, $extraParameters);
        }
        // put identity data in call if available
        if ($identityData) {
            $call['identity'] = $identityData;
        }
        // Prepare keys in alphabetical order
        ksort($call);
        return $call;
    }
}
