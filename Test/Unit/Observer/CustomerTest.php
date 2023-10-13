<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Newsletter\Model\Subscriber;
use Metrilo\Analytics\Api\Client;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\CustomerSerializer;
use Metrilo\Analytics\Model\Events\CustomEventFactory;
use Metrilo\Analytics\Model\Events\IdentifyCustomerFactory;
use Metrilo\Analytics\Model\MetriloCustomer;
use Metrilo\Analytics\Model\MetriloCustomerFactory;
use Metrilo\Analytics\Observer\Customer;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\CustomEvent;
use Metrilo\Analytics\Model\Events\IdentifyCustomer;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTest extends TestCase
{
    private Observer $observer;

    private CustomerRepositoryInterface $customerRepositoryInterface;

    private GroupRepositoryInterface $groupRepositoryInterface;

    private Subscriber $subscriberModel;

    private Data $dataHelper;

    private ApiClient $apiClientHelper;

    private Client $client;

    private CustomerSerializer $customerSerializerHelper;

    private Customer $customerObserver;

    private SessionEvents $sessionEvents;

    private MetriloCustomerFactory $customerFactory;

    private CustomEventFactory $customEventFactory;

    private IdentifyCustomerFactory $identifyCustomerFactory;

    public function setUp(): void
    {
        $this->observer = $this->getMockBuilder(Observer::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getEvent', 'getCustomer', 'getName', 'getSubscriber'])
                               ->getMock();

        $this->customerRepositoryInterface = $this->getMockBuilder(CustomerRepositoryInterface::class)
                                                  ->disableOriginalConstructor()
                                                  ->setMethods(
                                                      ['save', 'getList', 'delete', 'deleteById', 'getById', 'get']
                                                  )
                                                  ->getMock();

        $this->groupRepositoryInterface = $this->getMockBuilder(GroupRepositoryInterface::class)
                                               ->disableOriginalConstructor()
                                               ->setMethods(['save', 'getList', 'delete', 'deleteById', 'getById'])
                                               ->getMock();

        $this->subscriberModel = $this->getMockBuilder(Subscriber::class)
                                      ->disableOriginalConstructor()
                                      ->setMethods(['unsetData', 'loadByCustomerId', 'isSubscribed',])
                                      ->getMock();

        $this->dataHelper = $this->getMockBuilder(Data::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(['isEnabled', 'logError'])
                                 ->getMock();

        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
                                      ->disableOriginalConstructor()
                                      ->setMethods(['getClient'])
                                      ->getMock();

        $this->client = $this->getMockBuilder(Client::class)
                             ->disableOriginalConstructor()
                             ->setMethods(['customer'])
                             ->getMock();

        $this->customerSerializerHelper = $this->getMockBuilder(CustomerSerializer::class)
                                               ->disableOriginalConstructor()
                                               ->getMock();

        $customer = $this->getMockBuilder(MetriloCustomer::class)
                         ->disableOriginalConstructor()
                         ->setMethods([
                             'getStoreId',
                             'getEmail',
                             'getCreatedAt',
                             'getFirstName',
                             'getLastName',
                             'getSubscriberStatus',
                             'getTags'
                         ])
                         ->getMock();

        $this->customerFactory = $this->getMockBuilder(MetriloCustomerFactory::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->customerFactory->method('create')->will($this->returnValue($customer));

        $identifyEvent = $this->getMockBuilder(IdentifyCustomer::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['callJs'])
                              ->getMock();

        $this->identifyCustomerFactory = $this->getMockBuilder(IdentifyCustomerFactory::class)
                                              ->disableOriginalConstructor()
                                              ->getMock();
        $this->identifyCustomerFactory->method('create')->will($this->returnValue($identifyEvent));

        $customEvent = $this->getMockBuilder(CustomEvent::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['callJs'])
                            ->getMock();

        $this->customEventFactory = $this->getMockBuilder(CustomEventFactory::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->customEventFactory->method('create')->will($this->returnValue($customEvent));

        $this->sessionEvents = $this->getMockBuilder(SessionEvents::class)
                                    ->disableOriginalConstructor()
                                    ->setMethods(['addSessionEvent'])
                                    ->getMock();

        $this->customerObserver = new Customer(
            $this->dataHelper,
            $this->apiClientHelper,
            $this->customerSerializerHelper,
            $this->customerRepositoryInterface,
            $this->subscriberModel,
            $this->groupRepositoryInterface,
            $this->sessionEvents,
            $this->customerFactory,
            $this->identifyCustomerFactory,
            $this->customEventFactory
        );
    }

    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Customer(
                $this->dataHelper,
                $this->apiClientHelper,
                $this->customerSerializerHelper,
                $this->customerRepositoryInterface,
                $this->subscriberModel,
                $this->groupRepositoryInterface,
                $this->sessionEvents,
                $this->customerFactory,
                $this->identifyCustomerFactory,
                $this->customEventFactory
            )
        );
    }

    public function testExecute()
    {
        $storeId = 1;

        $this->observer->expects($this->any())->method('getEvent')
                       ->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getSubscriber')
                       ->will($this->returnValue($this->subscriberModel));

        $this->observer->expects($this->any())->method('getName')
            ->willReturnOnConsecutiveCalls(
                ['customer_save_after'],
                ['newsletter_subscriber_save_after'],
                ['customer_account_edited'],
                ['customer_register_success']
            );

        $this->sessionEvents->expects($this->any())->method('addSessionEvent')
                            ->with(
                                self::logicalOr(
                                    $this->isInstanceOf(IdentifyCustomer::class),
                                    $this->isInstanceOf(CustomEvent::class)
                                )
                            );

        $this->dataHelper->expects($this->any())->method('isEnabled')->with($this->isType('int'))
                         ->will($this->returnValue(true));
        $this->dataHelper->expects($this->any())->method('logError')->with($this->isType('object'));

        $this->apiClientHelper->expects($this->any())->method('getClient')
                              ->with($this->equalTo($storeId))
                              ->will($this->returnValue($this->client));

        $this->client->expects($this->any())->method('customer')
                     ->with($this->isInstanceOf(CustomerSerializer::class));

        $this->customerSerializerHelper->expects($this->any())->method('serialize')
                                       ->with($this->isInstanceOf(CustomerSerializer::class))
                                       ->will($this->returnValue([]));

        $this->customerObserver->execute($this->observer);
    }
}
