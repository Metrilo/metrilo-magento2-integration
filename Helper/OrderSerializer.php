<?php

namespace Metrilo\Analytics\Helper;

class OrderSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function serializeOrder($order) {
    
        $orderItems    = $order->getAllItems();
        $orderProducts = [];
    
        foreach ($orderItems as $orderItem) {
            $itemType = $orderItem->getProductType();
            if ($itemType == 'configurable' || $itemType == 'bundle') { // exclude configurable/bundle parent product returned by getAllItems() method
                continue;
            }
            $orderProducts[] = [
                'productId'  => $orderItem->getProductId(),
                'quantity'   => $orderItem->getQtyOrdered()
            ];
        }
    
        $orderBillingData = $order->getBillingAddress();
        $street           = $orderBillingData->getStreet();
        $couponCode       = $order->getCouponCode() ? [$order->getCouponCode()] : [];
    
        $orderBilling = [
            "firstName"     => $orderBillingData->getFirstname(),
            "lastName"      => $orderBillingData->getLastname(),
            "address"       => is_array($street) ? implode(PHP_EOL, $street) : $street,
            "city"          => $orderBillingData->getCity(),
            "countryCode"   => $orderBillingData->getCountryId(),
            "phone"         => $orderBillingData->getTelephone(),
            "postcode"      => $orderBillingData->getPostcode(),
            "paymentMethod" => $order->getPayment()->getMethodInstance()->getTitle()
        ];
    
        $serializedOrder = [
            'id'        => $order->getIncrementId(),
            'createdAt' => strtotime($order->getCreatedAt()) * 1000,
            'email'     => $order->getCustomerEmail(),
            'amount'    => $order->getBaseGrandTotal(),
            'coupons'   => $couponCode,
            'status'    => $order->getStatus(),
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
    
        return $serializedOrder;
    }
}
