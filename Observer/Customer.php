<?php

namespace Metrilo\Analytics\Observer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\CustomerSerializer;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\CustomEventFactory;
use Metrilo\Analytics\Model\Events\IdentifyCustomerFactory;
use Metrilo\Analytics\Model\MetriloCustomerFactory;

class Customer implements ObserverInterface
{
    private Data $helper;

    private ApiClient $apiClient;

    private CustomerSerializer $customerSerializer;

    private CustomerRepositoryInterface $customerRepository;

    private Subscriber $subscriberModel;

    private SessionEvents $sessionEvents;

    private GroupRepositoryInterface $groupRepository;

    private MetriloCustomerFactory $customerFactory;

    private IdentifyCustomerFactory $identifyCustomerFactory;

    private CustomEventFactory $customEventFactory;

    /**
     * @param Data $helper
     * @param ApiClient $apiClient
     * @param CustomerSerializer $customerSerializer
     * @param CustomerRepositoryInterface $customerRepository
     * @param Subscriber $subscriberModel
     * @param GroupRepositoryInterface $groupRepository
     * @param SessionEvents $sessionEvents
     * @param MetriloCustomerFactory $customerFactory
     * @param IdentifyCustomerFactory $identifyCustomerFactory
     * @param CustomEventFactory $customEventFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Data $helper,
        ApiClient $apiClient,
        CustomerSerializer $customerSerializer,
        CustomerRepositoryInterface $customerRepository,
        Subscriber $subscriberModel,
        GroupRepositoryInterface $groupRepository,
        SessionEvents $sessionEvents,
        MetriloCustomerFactory $customerFactory,
        IdentifyCustomerFactory $identifyCustomerFactory,
        CustomEventFactory $customEventFactory
    ) {
        $this->helper = $helper;
        $this->apiClient = $apiClient;
        $this->customerSerializer = $customerSerializer;
        $this->customerRepository = $customerRepository;
        $this->subscriberModel = $subscriberModel;
        $this->groupRepository = $groupRepository;
        $this->sessionEvents = $sessionEvents;
        $this->customerFactory = $customerFactory;
        $this->identifyCustomerFactory = $identifyCustomerFactory;
        $this->customEventFactory = $customEventFactory;
    }

    private function getCustomerFromEvent($observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'customer_save_after':
                $customer = $observer->getEvent()->getCustomer();
                if ($this->hasCustomerChanged($customer)) {
                    return $this->customerFactory->create(
                        [
                            'storeId' => $customer->getStoreId(),
                            'email' => $customer->getEmail(),
                            'createAt' => strtotime($customer->getCreatedAt()) * 1000,
                            'firstname' => $customer->getData('firstname'),
                            'lastname' => $customer->getData('lastname'),
                            'subscribed' => $this->getCustomerSubscriberStatus($customer->getId()),
                            'tags' => $this->getCustomerGroup($customer->getGroupId())
                        ]
                    );
                }
                break;
            case 'newsletter_subscriber_save_after':
                $subscriber = $observer->getEvent()->getSubscriber();
                $customerId = $subscriber->getCustomerId();
                if ($subscriber->isStatusChanged() && $customerId !== 0) {
                    return $this->metriloCustomer($this->customerRepository->getById($customerId));
                } else {
                    $subscriberEmail = $subscriber->getEmail();
                    $identifyCustomer = $this->identifyCustomerFactory->create(['email' => $subscriberEmail]);
                    $customEvent = $this->customEventFactory->create(['customEvent' => 'Subscribed']);

                    $this->sessionEvents->addSessionEvent($identifyCustomer->callJs());
                    $this->sessionEvents->addSessionEvent($customEvent->callJs());

                    return $this->customerFactory->create(
                        [
                            'storeId' => $subscriber->getStoreId(),
                            'email' => $subscriberEmail,
                            'createdAt' => time() * 1000,
                            'firstName' => '',
                            'lastName' => '',
                            'subscribed' => true,
                            'tags' => ['Newsletter']
                        ]
                    );
                }
            case 'customer_account_edited':
                return $this->metriloCustomer($this->customerRepository->get($observer->getEvent()->getEmail()));
            case 'customer_register_success':
                return $this->metriloCustomer($observer->getEvent()->getCustomer());
            case 'sales_order_save_after':
                return $this->customerFactory->create(
                    [
                        'storeId' => $observer->getEvent()->getOrder()->getStoreId(),
                        'email' => $observer->getEvent()->getOrder()->getCustomerEmail(),
                        'createdAt' => strtotime($observer->getEvent()->getOrder()->getCreatedAt()) * 1000,
                        'firstName' => $observer->getEvent()->getOrder()->getBillingAddress()->getData('firstname'),
                        'lastName' => $observer->getEvent()->getOrder()->getBillingAddress()->getData('lastname'),
                        'subscribed' => true,
                        'tags' => ['guest_customer']
                    ]
                );
            default:
                break;
        }

        return false;
    }

    private function hasCustomerChanged($customer)
    {
        $originalCustomer = $this->customerRepository->getById($customer->getId());

        if ($originalCustomer->getCreatedAt() === $originalCustomer->getUpdatedAt()) {
            return true; // if customer is created in admin there are no differences in $customer and $originalCustomer
        }

        return $customer->getEmail() != $originalCustomer->getEmail() ||
            $customer->getFirstname() != $originalCustomer->getFirstname() ||
            $customer->getLastname() != $originalCustomer->getLastname() ||
            $customer->getGroupId() != $originalCustomer->getGroupId();
    }

    private function getCustomerSubscriberStatus($customerId)
    {
        $this->subscriberModel->unsetData();

        return $this->subscriberModel->loadByCustomerId($customerId)->isSubscribed();
    }

    private function getCustomerGroup($groupId)
    {
        $group = $this->groupRepository->getById($groupId);
        $groupName[] = $group->getCode();

        return $groupName;
    }

    private function metriloCustomer($customer)
    {
        return $this->customerFactory->create(
            [
                'storeId' => $customer->getStoreId(),
                'email' => $customer->getEmail(),
                'createdAt' => strtotime($customer->getCreatedAt()) * 1000,
                'firstname' => $customer->getFirstName(),
                'lastname' => $customer->getLastName(),
                'subscribed' => $this->getCustomerSubscriberStatus($customer->getId()),
                'tags' => $this->getCustomerGroup($customer->getGroupId())
            ]
        );
    }

    public function execute(Observer $observer)
    {
        try {
            $customer = $this->getCustomerFromEvent($observer);
            if ($customer && $this->helper->isEnabled($customer->getStoreId())) {
                if (!trim($customer->getEmail())) {
                    $this->helper
                        ->logError('Customer with id = ' . $customer->getId() . '  has no email address!');
                    return;
                }

                $client = $this->apiClient->getClient($customer->getStoreId());
                $serializedCustomer = $this->customerSerializer->serialize($customer);
                $client->customer($serializedCustomer);
            }
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }
}
