<?php

namespace Metrilo\Analytics\Model;

class OrderData
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInterface
    ) {
        $this->orderCollection  = $orderCollection;
        $this->countryInterface = $countryInterface;
    }

    public function getOrders($storeId)
    {
        $ordersArray   = [];
        $orders        = $this->getOrderQuery($storeId);

        foreach ($orders as $order) {
            if(!$order->getCustomerEmail()) {
                continue;
            }
            $orderItems = $order->getAllItems();
            $orderProducts = [];
            
            foreach ($orderItems as $orderItem) {
                $itemType = $orderItem->getProductType();
                if ($itemType == 'configurable' || $itemType == 'bundle') { // exclude configurable/bundle parent product returned by getAllItems() method
                    continue;
                }
                $orderProducts[] = [
                    'productId' => $orderItem->getProductId(),
                    'quantity' => $orderItem->getQtyOrdered()
                ];
            }

            $orderBillingData = $order->getBillingAddress();
            $countryData      = $this->countryInterface->getCountryInfo($orderBillingData->getCountryId());
            $street           = $orderBillingData->getStreet();
            $couponCode[]     = (!empty($order->getCouponCode())) ? $order->getCouponCode() : '';

            $orderBilling = [
                "firstName"     => $orderBillingData->getFirstname(),
                "lastName"      => $orderBillingData->getLastname(),
                "address"       => is_array($street) ? implode(PHP_EOL, $street) : $street,
                "city"          => $orderBillingData->getCity(),
                "country"       => $countryData->getFullNameEnglish(),
                "phone"         => $orderBillingData->getTelephone(),
                "postcode"      => $orderBillingData->getPostcode(),
                "paymentMethod" => $order->getPayment()->getMethodInstance()->getTitle()
            ];

            $ordersArray[] = [
                'id'        => $order->getIncrementId(),
                'createdAt' => strtotime($order->getCreatedAt()),
                'updatedAt' => strtotime($order->getUpdatedAt()),
                'email'     => $order->getCustomerEmail(),
                'amount'    => $order->getBaseGrandTotal(),
                'coupons'   => $couponCode, // RETURNING NULL if there is no coupon applied // MULTYPLE coupon codes are available via extension ONLY
                'status'    => $order->getStatus(),
                'products'  => $orderProducts,
                'billing'   => $orderBilling
            ];

            unset($couponCode);
        }

        return $ordersArray;
    }

    protected function getOrderQuery($storeId = 0)
    {
        return $this->orderCollection->create()->addAttributeToFilter('store_id', $storeId)->addAttributeToSelect('*')->setOrder('entity_id', 'asc');
    }

}
