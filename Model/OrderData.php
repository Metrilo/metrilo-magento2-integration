<?php

namespace Metrilo\Analytics\Model;

class OrderData
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
    ) {
        $this->orderCollection  = $orderCollection;
    }

    public function getOrders($storeId, $chunkId, $chunkItems)
    {
        $ordersArray = [];
        $orders      = $this->getOrderQuery($storeId)
                            ->setPageSize($chunkItems)
                            ->setCurPage($chunkId + 1);

        foreach ($orders as $order) {
            if(!trim($order->getCustomerEmail())) {
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
                "country"       => $orderBillingData->getCountryId(),
                "phone"         => $orderBillingData->getTelephone(),
                "postcode"      => $orderBillingData->getPostcode(),
                "paymentMethod" => $order->getPayment()->getMethodInstance()->getTitle()
            ];

            $ordersArray[] = [
                'id'        => $order->getIncrementId(),
                'createdAt' => strtotime($order->getCreatedAt()),
                'email'     => $order->getCustomerEmail(),
                'amount'    => $order->getBaseGrandTotal(),
                'coupons'   => $couponCode,
                'status'    => $order->getStatus(),
                'products'  => $orderProducts,
                'billing'   => $orderBilling
            ];
        }

        return $ordersArray;
    }

    public function getOrderQuery($storeId = 0)
    {
        return $this->orderCollection->create()->addAttributeToFilter('store_id', $storeId)->addAttributeToSelect('*')->setOrder('entity_id', 'asc');
    }

}
