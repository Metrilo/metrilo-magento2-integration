<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection as AddressCollection;
use Metrilo\Analytics\Helper\OrderSerializer;

class OrderSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;
    
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderModel;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $orderCollection;
    
    /**
     * @var \Magento\OfflinePayments\Model\Cashondelivery
     */
    private $cashOnDeliveryModel;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Collection
     */
    private $orderPaymentModelCollection;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\Collection
     */
    private $orderAddressModelCollection;

    /**
     * @var \Metrilo\Analytics\Helper\OrderSerializer
     */
    private $orderSerializer;
    
    private $orderModelMethods = [
        'getMethodInstance',
        'getAllItems',
        'getBillingAddress',
        'getCouponCode',
        'getPayment',
        'getCustomerEmail',
        'getIncrementId',
        'getCreatedAt',
        'getBaseGrandTotal',
        'getTotalRefunded',
        'getStatus'];
    private $customerEmail     = 'customer@email.com';
    private $incrementId       = 10000000001;
    private $createdAt         = '01.01.2020';
    private $couponCode        = 'CouponCode';
    private $baseGrandTotal    = 100;
    private $totalRefunded     = 30;
    private $orderStatus       = 'orderStatus';

    public function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->orderModel = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods($this->orderModelMethods)
            ->getMock();
    
        $this->orderCollection = $this->getMockBuilder(OrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods($this->orderModelMethods)
            ->getMock();
        
        $this->cashOnDeliveryModel = $this->getMockBuilder(Cashondelivery::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getTitle'])
            ->getMock();
    
        $this->cashOnDeliveryModel->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->cashOnDeliveryModel->expects($this->any())->method('getTitle')
            ->will($this->returnValue('Payment Method Name'));
        
        $this->orderPaymentModelCollection = $this->getMockBuilder(PaymentCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    
        $this->orderAddressModelCollection = $this->getMockBuilder(PaymentCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'create',
                'getTelephone',
                'getStreet',
                'getFirstName',
                'getLastname',
                'getCity',
                'getCountryId',
                'getPostcode'
            ])
            ->getMock();
    
        $this->orderSerializer = new OrderSerializer($this->context);
    }
    
    public function testSerialize()
    {
        $this->orderModel->expects($this->any())->method('getAllItems')
            ->will($this->returnValue([]));
        $this->orderModel->expects($this->any())->method('getMethodInstance')
            ->will($this->returnValue($this->cashOnDeliveryModel));
        $this->orderModel->expects($this->any())->method('getBillingAddress')
            ->will($this->returnValue($this->orderAddressModelCollection));
        $this->orderModel->expects($this->any())->method('getPayment')
            ->will($this->returnSelf());
        $this->orderModel->expects($this->any())->method('getBillingAddress')
            ->will($this->returnSelf());
        $this->orderModel->expects($this->any())->method('getCustomerEmail')
            ->will($this->returnValue($this->customerEmail));
        $this->orderModel->expects($this->any())->method('getIncrementId')
            ->will($this->returnValue($this->incrementId));
        $this->orderModel->expects($this->any())->method('getCreatedAt')
            ->will($this->returnValue($this->createdAt));
        $this->orderModel->expects($this->any())->method('getCouponCode')
            ->will($this->returnValue($this->couponCode));
        $this->orderModel->expects($this->any())->method('getBaseGrandTotal')
            ->will($this->returnValue($this->baseGrandTotal));
        $this->orderModel->expects($this->any())->method('getTotalRefunded')
            ->will($this->returnValue($this->totalRefunded));
        $this->orderModel->expects($this->any())->method('getStatus')
            ->will($this->returnValue($this->orderStatus));
        
        $this->orderCollection->expects($this->any())->method('getAllItems')
            ->will($this->returnValue([]));
        $this->orderCollection->expects($this->any())->method('getMethodInstance')
            ->will($this->returnValue($this->cashOnDeliveryModel));
        $this->orderCollection->expects($this->any())->method('getBillingAddress')
            ->will($this->returnValue($this->orderAddressModelCollection));
        $this->orderCollection->expects($this->any())->method('getPayment')
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('getCustomerEmail')
            ->will($this->returnValue($this->customerEmail));
        $this->orderCollection->expects($this->any())->method('getIncrementId')
            ->will($this->returnValue($this->incrementId));
        $this->orderCollection->expects($this->any())->method('getCreatedAt')
            ->will($this->returnValue($this->createdAt));
        $this->orderCollection->expects($this->any())->method('getCouponCode')
            ->will($this->returnValue($this->couponCode));
        $this->orderCollection->expects($this->any())->method('getBaseGrandTotal')
            ->will($this->returnValue($this->baseGrandTotal));
        $this->orderCollection->expects($this->any())->method('getTotalRefunded')
            ->will($this->returnValue($this->totalRefunded));
        $this->orderCollection->expects($this->any())->method('getStatus')
            ->will($this->returnValue($this->orderStatus));
    
        $this->orderAddressModelCollection->expects($this->any())->method('getTelephone')
            ->will($this->returnValue('0883123456'));
        $this->orderAddressModelCollection->expects($this->any())->method('getStreet')
            ->will($this->returnValue('streetAddress'));
        $this->orderAddressModelCollection->expects($this->any())->method('getFirstName')
            ->will($this->returnValue('firstName'));
        $this->orderAddressModelCollection->expects($this->any())->method('getLastname')
            ->will($this->returnValue('lastName'));
        $this->orderAddressModelCollection->expects($this->any())->method('getCity')
            ->will($this->returnValue('city'));
        $this->orderAddressModelCollection->expects($this->any())->method('getCountryId')
            ->will($this->returnValue('countryId'));
        $this->orderAddressModelCollection->expects($this->any())->method('getPostcode')
            ->will($this->returnValue('postCode'));
    
        $dataModelTest = $this->orderSerializer->serialize($this->orderCollection);
        $observerTest  = $this->orderSerializer->serialize($this->orderModel);
        
        $this->assertEquals($observerTest, $dataModelTest);
    }
}
