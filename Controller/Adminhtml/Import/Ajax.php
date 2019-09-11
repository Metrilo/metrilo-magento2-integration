<?php

namespace Metrilo\Analytics\Controller\Adminhtml\Import;
use \Metrilo\Analytics\Api\Client;

class Ajax extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context                $context,
        \Metrilo\Analytics\Helper\Data                     $helper,
        \Metrilo\Analytics\Model\CustomerData              $customerData,
        \Metrilo\Analytics\Model\CategoryData              $categoryData,
        \Metrilo\Analytics\Model\ProductData               $productData,
        \Metrilo\Analytics\Model\DeletedProductData        $deletedProductData,
        \Metrilo\Analytics\Model\OrderData                 $orderData,
        \Metrilo\Analytics\Helper\CustomerSerializer       $customerSerializer,
        \Metrilo\Analytics\Helper\CategorySerializer       $categorySerializer,
        \Metrilo\Analytics\Helper\ProductSerializer        $productSerializer,
        \Metrilo\Analytics\Helper\DeletedProductSerializer $deletedProductSerializer,
        \Metrilo\Analytics\Helper\OrderSerializer          $orderSerializer,
        \Metrilo\Analytics\Helper\ApiClient                $apiClient,
        \Metrilo\Analytics\Helper\Activity                 $activityHelper,
        \Magento\Framework\App\Request\Http                $request,
        \Magento\Framework\Controller\Result\JsonFactory   $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helper                   = $helper;
        $this->customerData             = $customerData;
        $this->categoryData             = $categoryData;
        $this->productData              = $productData;
        $this->deletedProductData       = $deletedProductData;
        $this->orderData                = $orderData;
        $this->customerSerializer       = $customerSerializer;
        $this->categorySerializer       = $categorySerializer;
        $this->productSerializer        = $productSerializer;
        $this->deletedProductSerializer = $deletedProductSerializer;
        $this->orderSerializer          = $orderSerializer;
        $this->apiClient                = $apiClient;
        $this->activityHelper           = $activityHelper;
        $this->request                  = $request;
        $this->resultJsonFactory        = $resultJsonFactory;
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
            $client            = $this->apiClient->getClient($storeId);
            
            switch ($importType) {
                case 'customers':
                    if ($chunkId == 0) {
                        $this->activityHelper->createActivity($storeId, 'import_start');
                    }
                    $serializedCustomers = $this->serializeRecords($this->customerData->getCustomers($storeId, $chunkId), $this->customerSerializer);
                    $result['success']   = $client->customerBatch($serializedCustomers);
                    break;
                case 'categories':
                    $serializedCategories = $this->serializeRecords($this->categoryData->getCategories($storeId, $chunkId), $this->categorySerializer);
                    $result['success']    = $client->categoryBatch($serializedCategories);
                    break;
                case 'deletedProducts':
                    $deletedProductOrders = $this->deletedProductData->getDeletedProductOrders($storeId);
                    if ($deletedProductOrders) {
                        $serializedDeletedProducts = $this->deletedProductSerializer->serialize($deletedProductOrders);
                        $deletedProductChunks = array_chunk($serializedDeletedProducts, $this->helper::chunkItems);
                        foreach($deletedProductChunks as $chunk) {
                            $client->productBatch($chunk);
                        }
                    }
                    break;
                case 'products':
                    $serializedProducts = $this->serializeRecords($this->productData->getProducts($storeId, $chunkId), $this->productSerializer);
                    $result['success']  = $client->productBatch($serializedProducts);
                    break;
                case 'orders':
                    $serializedOrders  = $this->serializeRecords($this->orderData->getOrders($storeId, $chunkId), $this->orderSerializer);
                    $result['success'] = $client->orderBatch($serializedOrders);
                    if ($chunkId == (int)$this->request->getParam('ordersChunks') - 1) {
                        $this->activityHelper->createActivity($storeId, 'import_end');
                    }
                    break;
                default:
                    $result['success'] = false;
                    break;
            }

            return $jsonFactory->setData($result);
        } catch (\Exception $e) {
            $this->helper->logError($e);
            
            return $jsonFactory->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function serializeRecords($records, $serializer) {
        $serializedData = [];
        
        foreach($records as $record) {
            $serializedRecord = $serializer->serialize($record);
            if ($serializedRecord) {
                $serializedData[] = $serializedRecord;
            }
        }
        
        return $serializedData;
    }
}
