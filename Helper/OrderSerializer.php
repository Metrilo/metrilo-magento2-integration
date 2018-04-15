<?php

namespace Metrilo\Analytics\Helper;

class OrderSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
    ) {
        $this->imageHelperFactory = $imageHelperFactory;
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
            'quote_id'           => $order->getQuoteId(),
            'order_status'      => $order->getStatus(),
            'amount'            => (float)$order->getGrandTotal(),
            'shipping_amount'   => (float)$order->getShippingAmount(),
            'tax_amount'        => $order->getTaxAmount(),
            'items'             => [],
            'shipping_method'   => $order->getShippingDescription(),
            'payment_method'    => $order->getPayment()->getMethodInstance()->getTitle(),
        ];

        $this->assignBillingInfo($data, $order);

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

            $mainProduct = $item->getProduct();

            if ($item->getProductType() == 'configurable') {
                $parentId = $item->getProductId();
                $mainProduct = $this->productRepository->getById($parentId);
                $options = (array)$item->getProductOptions();
                $dataItem['option_id'] = $options['simple_sku'];
                // for legacy reasons - we have been passing the SKU as ID for the child products
                $dataItem['option_sku'] = $options['simple_sku'];
                $dataItem['option_name'] = $options['simple_name'];
                $dataItem['option_price'] = (float)$item->getPrice();
            }

            if($mainProduct->getImage()) {
                $imageUrl = $this->imageHelperFactory->create()
                    ->init($mainProduct, 'product_thumbnail_image')->getUrl();
                $dataItem['image_url'] = $imageUrl;
            }

            $dataItem['sku'] = $mainProduct->getSku();
            $data['items'][] = $dataItem;
        }
        return $data;
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
        // Prepare keys is alphabetical order
        ksort($call);
        return $call;
    }
}
