<?php
    
namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerAdminUpdate implements ObserverInterface
{
    
    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Metrilo\Analytics\Helper\ApiClient               $apiClient
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Helper\ApiClient $apiClient,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->customerRepository = $customerRepository;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $customer     = $observer->getEvent()->getCustomer();
            $origCustomer = $this->customerRepository->getById($customer->getId());
            $storeId      = $this->helper->getStoreId();
            
            if (!trim($customer->getEmail())) {
                $this->helper->logError('Customer with id = '. $customer->getId(). '  have no email address!');
                return;
            }
            
            // Create api call only if there is difference between original and saved after customer data.
            if ($customer->getEmail() == $origCustomer->getEmail() &&
                $customer->getFirstname() == $origCustomer->getFirstname() &&
                $customer->getLastname() == $origCustomer->getLastname()) {
                $hasChanges = false;
            } else {
                $hasChanges = true;
            }
            
            if ($hasChanges) {
                $client             = $this->apiClient->getClient($storeId);
                $serializedCustomer = $this->helper->customerSerializer->serializeCustomer($customer);
                $client->customer($serializedCustomer);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
