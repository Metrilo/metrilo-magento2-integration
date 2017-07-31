<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerUpdate implements ObserverInterface
{

    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Track customer update profile information
     * and trigger "identify" to Metrilo
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $email = $observer->getEvent()->getEmail();
            if (empty($email) || !$email) {
                return;
            }

            $customer = $this->customerRepository->get($email);
            if ($customer) {
                $data = [
                    'id' => $customer->getEmail(),
                    'params' => [
                        'email'         => $customer->getEmail(),
                        'name'          => $customer->getFirstname() .' '.$customer->getLastname(),
                        'first_name'    => $customer->getFirstname(),
                        'last_name'     => $customer->getLastname(),
                    ]
                ];
                $this->helper->addSessionEvent('identify', 'identify', $data);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
