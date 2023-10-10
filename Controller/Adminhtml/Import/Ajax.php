<?php

namespace Metrilo\Analytics\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Metrilo\Analytics\Helper\Activity;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\CategorySerializer;
use Metrilo\Analytics\Helper\CustomerSerializer;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\DeletedProductSerializer;
use Metrilo\Analytics\Helper\OrderSerializer;
use Metrilo\Analytics\Helper\ProductSerializer;
use Metrilo\Analytics\Model\CategoryData;
use Metrilo\Analytics\Model\CustomerData;
use Metrilo\Analytics\Model\DeletedProductData;
use Metrilo\Analytics\Model\OrderData;
use Metrilo\Analytics\Model\ProductData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ajax implements HttpPostActionInterface
{
    private Data $helper;

    private CustomerData $customerData;

    private CategoryData $categoryData;

    private DeletedProductData $deletedProductData;

    private ProductData $productData;

    private CustomerSerializer $customerSerializer;

    private CategorySerializer $categorySerializer;

    private JsonFactory $resultJsonFactory;

    private ApiClient $apiClient;

    private OrderSerializer $orderSerializer;

    private DeletedProductSerializer $deletedProductSerializer;

    private ProductSerializer $productSerializer;

    private OrderData $orderData;

    private Activity $activityHelper;

    private Http $request;

    /**
     * @param Data $helper
     * @param CustomerData $customerData
     * @param CategoryData $categoryData
     * @param ProductData $productData
     * @param DeletedProductData $deletedProductData
     * @param OrderData $orderData
     * @param CustomerSerializer $customerSerializer
     * @param CategorySerializer $categorySerializer
     * @param ProductSerializer $productSerializer
     * @param DeletedProductSerializer $deletedProductSerializer
     * @param OrderSerializer $orderSerializer
     * @param ApiClient $apiClient
     * @param Activity $activityHelper
     * @param Http $request
     * @param JsonFactory $resultJsonFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Data $helper,
        CustomerData $customerData,
        CategoryData $categoryData,
        ProductData $productData,
        DeletedProductData $deletedProductData,
        OrderData $orderData,
        CustomerSerializer $customerSerializer,
        CategorySerializer $categorySerializer,
        ProductSerializer $productSerializer,
        DeletedProductSerializer $deletedProductSerializer,
        OrderSerializer $orderSerializer,
        ApiClient $apiClient,
        Activity $activityHelper,
        Http $request,
        JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->customerData = $customerData;
        $this->categoryData = $categoryData;
        $this->productData = $productData;
        $this->deletedProductData = $deletedProductData;
        $this->orderData = $orderData;
        $this->customerSerializer = $customerSerializer;
        $this->categorySerializer = $categorySerializer;
        $this->productSerializer = $productSerializer;
        $this->deletedProductSerializer = $deletedProductSerializer;
        $this->orderSerializer = $orderSerializer;
        $this->apiClient = $apiClient;
        $this->activityHelper = $activityHelper;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Import history by chunks
     *
     * @return ResultInterface
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(): ResultInterface
    {
        $result['success'] = false;
        $jsonResponse = $this->resultJsonFactory->create();
        try {
            $storeId = (int)$this->request->getParam('storeId');
            $chunkId = (int)$this->request->getParam('chunkId');
            $importType = (string)$this->request->getParam('importType');
            $client = $this->apiClient->getClient($storeId);

            switch ($importType) {
                case 'customers':
                    if ($chunkId == 0) {
                        $this->activityHelper->createActivity($storeId, 'import_start');
                    }
                    $serializedCustomers = $this->serializeRecords(
                        $this->customerData->getCustomers($storeId, $chunkId),
                        $this->customerSerializer
                    );
                    $result['success'] = $client->customerBatch($serializedCustomers);
                    break;
                case 'categories':
                    $serializedCategories = $this->serializeRecords(
                        $this->categoryData->getCategories($storeId, $chunkId),
                        $this->categorySerializer
                    );
                    $result['success'] = $client->categoryBatch($serializedCategories);
                    break;
                case 'deletedProducts':
                    $deletedProductOrders = $this->deletedProductData->getDeletedProductOrders($storeId);
                    if ($deletedProductOrders) {
                        $helperObject = $this->helper; // for backward compatibility for php ~5.5, ~5.6
                        $serializedDeletedProducts = $this->deletedProductSerializer->serialize($deletedProductOrders);
                        $deletedProductChunks = array_chunk($serializedDeletedProducts, $helperObject::CHUNK_ITEMS);
                        foreach ($deletedProductChunks as $chunk) {
                            $client->productBatch($chunk);
                        }
                    }
                    break;
                case 'products':
                    $serializedProducts = $this->serializeRecords(
                        $this->productData->getProducts($storeId, $chunkId),
                        $this->productSerializer
                    );
                    $result['success'] = $client->productBatch($serializedProducts);
                    break;
                case 'orders':
                    $serializedOrders = $this->serializeRecords(
                        $this->orderData->getOrders($storeId, $chunkId),
                        $this->orderSerializer
                    );
                    $result['success'] = $client->orderBatch($serializedOrders);
                    if ($chunkId == (int)$this->request->getParam('ordersChunks') - 1) {
                        $this->activityHelper->createActivity($storeId, 'import_end');
                    }
                    break;
                default:
                    $result['success'] = false;
                    break;
            }

            return $jsonResponse->setData($result);
        } catch (\Exception $e) {
            $this->helper->logError($e);

            return $jsonResponse->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function serializeRecords($records, $serializer)
    {
        $serializedData = [];

        foreach ($records as $record) {
            $serializedRecord = $serializer->serialize($record);
            if ($serializedRecord) {
                $serializedData[] = $serializedRecord;
            }
        }

        return $serializedData;
    }
}
