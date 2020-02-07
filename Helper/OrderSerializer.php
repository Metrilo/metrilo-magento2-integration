<?php

namespace Metrilo\Analytics\Helper;

class OrderSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function serialize($order) {
    
        $orderItems    = $order->getAllItems();
        $orderProducts = [];
    
        foreach ($orderItems as $orderItem) {
            $itemType = $orderItem->getProductType();
            if ($itemType == 'configurable') { // exclude configurable parent product returned by getAllItems() method
                continue;
            }
            
            $orderItemSku = $orderItem->getData('sku');
            $orderProducts[] = [
                'productId'  => $orderItemSku ? $orderItemSku : $orderItem->getProductId(),
                'quantity'   => $orderItem->getQtyOrdered()
            ];
        }
    
        $orderBillingData = $order->getBillingAddress();
        $orderPhone       = $orderBillingData->getTelephone();
        $street           = $orderBillingData->getStreet();
        $couponCode       = $order->getCouponCode() ? [$order->getCouponCode()] : [];
    
        $orderBilling = [
            "firstName"     => $orderBillingData->getFirstname(),
            "lastName"      => $orderBillingData->getLastname(),
            "address"       => is_array($street) ? implode(PHP_EOL, $street) : $street,
            "city"          => $orderBillingData->getCity(),
            "countryCode"   => $orderBillingData->getCountryId(),
            "phone"         => $orderPhone,
            "postcode"      => $orderBillingData->getPostcode(),
            "paymentMethod" => $order->getPayment()->getMethodInstance()->getTitle()
        ];
        
        if (!empty($order->getCustomerEmail())) {
            $customerEmail = $order->getCustomerEmail();
        } elseif (!empty($orderPhone)) {
            $customerEmail = $orderPhone . '@phone_email';
        } else {
            return false;
        }
        
        $serializedOrder = [
            'id'        => $order->getIncrementId(),
            'createdAt' => strtotime($order->getCreatedAt()) * 1000,
            'email'     => $customerEmail,
            'amount'    => $order->getBaseGrandTotal() - $order->getTotalRefunded(),
            'coupons'   => $couponCode,
            'status'    => $order->getStatus(),
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
    
        return $serializedOrder;
    }
}
