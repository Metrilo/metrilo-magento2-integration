<?php

namespace Metrilo\Analytics\Model;

class OrderData
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInterface
    ) {
        $this->orderCollection = $orderCollection;
        $this->countryInterface = $countryInterface;
    }

    public function getOrders($storeId)
    {
        $orders = $this->getOrderQuery($storeId);

        foreach ($orders as $order) {
            if($order->getCustomerEmail()) {
                $orderItems = $order->getAllItems(); // getAllVisibleItems() returns only parent products (for configurables) from order BUT ignoring child product quantities for BUNDLE products

                foreach ($orderItems as $orderItem) {
                    $test = $orderItem;
                    if ($orderItem->getProductType() != 'configurable' && $orderItem->getProductType() != 'bundle') { // TODO - test for bugs // exclude configurable/bundle product parent returned by getAllItems() method
                        $orderProducts[] = [
                            'productId' => $orderItem->getProductId(), // RETURNING PARENT PRODUCT ID IF CONFIGURABLE/BUNDLE PRODUCT
                            'quantity' => $orderItem->getQtyOrdered()
                        ];
                    }
                }

                $orderBillingData = $order->getBillingAddress();
                $countryData = $this->countryInterface->getCountryInfo($orderBillingData->getCountryId());
//              $street = $orderBillingData->getStreet(); //optional address parsing

                $orderBilling = [
                    "firstName"     => $orderBillingData->getFirstname(),
                    "lastName"      => $orderBillingData->getLastname(),
                    "address"       => implode(" ", $orderBillingData->getStreet()),
//                  "address"       => is_array($street) ? implode(PHP_EOL, $street) : $street, //optional address parsing
                    "city"          => $orderBillingData->getCity(),
                    "country"       => $countryData->getFullNameEnglish(), // Available option to get locale translation of the name using getFullNameLocale() method
                    "phone"         => $orderBillingData->getTelephone(),
                    "postcode"      => $orderBillingData->getPostcode(),
                    "paymentMethod" => $order->getPayment()->getMethodInstance()->getTitle()
                ];

                $ordersArray[] = [
                    'id'        => $order->getId(),
                    'createdAt' => strtotime($order->getCreatedAt()),
                    'updatedAt' => strtotime($order->getUpdatedAt()),
                    'email'     => $order->getCustomerEmail(),
                    'amount'    => $order->getBaseGrandTotal(),
                    'coupons'   => (!empty($order->getCouponCode())) ? $order->getCouponCode() : '', // RETURNING NULL if there is no coupon applied // MULTYPLE coupon codes are available via extension ONLY
                    'status'    => $order->getStatus(),
                    'products'  => (isset($orderProducts)) ? $orderProducts : '',
                    'billing'   => $orderBilling
                ];

                unset($orderProducts);
            }
        }

        return $ordersArray;
    }

    protected function getOrderQuery($storeId = 0)
    {
        return $this->orderCollection->create()->addAttributeToFilter('store_id', $storeId)->addAttributeToSelect('*')->setOrder('entity_id', 'asc');
    }

}
