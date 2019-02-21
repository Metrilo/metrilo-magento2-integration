<?php

namespace Metrilo\Analytics\Controller\Adminhtml\Import;
use \Metrilo\Analytics\Api\Client;
/**
 * AJAX Controller for sending chunks to Metrilo
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Ajax extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Metrilo\Analytics\Helper\Data                   $helper
     * @param \Metrilo\Analytics\Model\Import                  $import,
     * @param \Metrilo\Analytics\Model\OrderData               $orderData,
     * @param \Metrilo\Analytics\Helper\OrderSerializer        $orderSerializer,
     * @param \Metrilo\Analytics\Helper\ApiClient              $apiClient,
     * @param \Magento\Framework\App\Request\Http              $request
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
     # TODO: Ask Miro why \Magento\Framework|App\Action|Context won't compile
    public function __construct(
        \Magento\Backend\App\Action\Context              $context,
        \Metrilo\Analytics\Helper\Data                   $helper,
        \Metrilo\Analytics\Model\Import                  $import,
        \Metrilo\Analytics\Model\CustomerData            $customerData,
        \Metrilo\Analytics\Model\OrderData               $orderData,
        \Metrilo\Analytics\Helper\CustomerSerializer     $customerSerializer,
        \Metrilo\Analytics\Helper\OrderSerializer        $orderSerializer,
        \Metrilo\Analytics\Helper\ApiClient              $apiClient,
        \Magento\Framework\App\Request\Http              $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helper             = $helper;
        $this->import             = $import;
        $this->customerData       = $customerData;
        $this->orderData          = $orderData;
        $this->customerSerializer = $customerSerializer;
        $this->orderSerializer    = $orderSerializer;
        $this->apiClient          = $apiClient;
        $this->request            = $request;
        $this->resultJsonFactory  = $resultJsonFactory;
    }
    
    private function serializeDataBatch($importType, $dataBatch) {
        $serializedData = [];
        foreach($dataBatch as $data) {
            switch ($importType) {
                case 'customers':
                    $serializedData[] = $this->customerSerializer->serializeCustomer($data);
                    break;
                case 'categories':
                    break;
                case 'products':
                    break;
                case 'orders':
                    $serializedData[] = $this->orderSerializer->serializeOrder($data);
                    break;
                default:
                    break;
            }
        }
        
        return $serializedData;
    }
    
    private function sendSerializedData($importType, $serializedData, $storeId) {
        $client = $this->apiClient->getClient($storeId);
        
        if(!empty($serializedData)) {
            switch ($importType) {
                case 'customers':
                    $client->customerBatch($serializedData);
                    break;
                case 'categories':
                    break;
                case 'products':
                    break;
                case 'orders':
                    $client->orderBatch($serializedData);
                    break;
                default:
                    break;
            }
            $result = $importType . 'Batch';
        } else {
            $result = 'empty ' . $importType . 'Batch';
        }
        
        return $result;
    }

    /**
     * Import history by chunks
     *
     * @throws \Exception
     * @return string
     */
    public function execute()
    {
        try {
            $result['success'] = false;
            $jsonFactory       = $this->resultJsonFactory->create();
            $storeId           = (int)$this->request->getParam('storeId');
            $chunkId           = (int)$this->request->getParam('chunkId');
            $importType        = (string)$this->request->getParam('importType');

//            if ($chunkId == 0) {
//                $this->helper->createActivity($storeId, 'import_start');
//            }

            switch ($importType) {
                case 'customers':
                    $serializedCustomers = $this->serializeDataBatch($importType, $this->customerData->getCustomers($storeId, $chunkId));
                    $result['success']   = $this->sendSerializedData($importType, $serializedCustomers, $storeId);
                    break;
                case 'categories':
//                    $client->categoryBatch($this->import->categoryData->getCategories($storeId, $chunkId));
                    $result['success'] = 'categoryBatch';
                    break;
                case 'products':
//                    $client->productBatch($this->import->productData->getProducts($storeId, $chunkId));
                    $result['success'] = 'productBatch';
                    break;
                case 'orders':
                    $serializedOrders  = $this->serializeDataBatch($importType, $this->orderData->getOrders($storeId, $chunkId));
                    $result['success'] = $this->sendSerializedData($importType, $serializedOrders, $storeId);
                    break;
                default:
                    $result['success'] = false;
                    break;
            }

//            if ($chunkId == $totalChunks - 1) {
//                $this->helper->createActivity($storeId, 'import_end');
//            }

            return $jsonFactory->setData($result);
        } catch (\Exception $e) {
            return $jsonFactory->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}

