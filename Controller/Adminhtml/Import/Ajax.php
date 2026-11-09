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
                    $result = $this->batchResult($client->customerBatch($serializedCustomers));
                    break;
                case 'categories':
                    $serializedCategories = $this->serializeRecords(
                        $this->categoryData->getCategories($storeId, $chunkId),
                        $this->categorySerializer
                    );
                    $result = $this->batchResult($client->categoryBatch($serializedCategories));
                    break;
                case 'deletedProducts':
                    $result = $this->batchResult(null);
                    $deletedProductOrders = $this->deletedProductData->getDeletedProductOrders($storeId);
                    if ($deletedProductOrders) {
                        $helperObject = $this->helper; // for backward compatibility for php ~5.5, ~5.6
                        $serializedDeletedProducts = $this->deletedProductSerializer->serialize($deletedProductOrders);
                        $deletedProductChunks = array_chunk($serializedDeletedProducts, $helperObject::CHUNK_ITEMS);
                        foreach ($deletedProductChunks as $chunk) {
                            $result = $this->batchResult($client->productBatch($chunk));
                            if (!$result['success']) {
                                break;
                            }
                        }
                    }
                    break;
                case 'products':
                    $serializedProducts = $this->serializeRecords(
                        $this->productData->getProducts($storeId, $chunkId),
                        $this->productSerializer
                    );
                    $result = $this->batchResult($client->productBatch($serializedProducts));
                    break;
                case 'orders':
                    $serializedOrders = $this->serializeRecords(
                        $this->orderData->getOrders($storeId, $chunkId),
                        $this->orderSerializer
                    );
                    $result = $this->batchResult($client->orderBatch($serializedOrders));
                    if ($chunkId == (int)$this->request->getParam('ordersChunks') - 1) {
                        $this->activityHelper->createActivity($storeId, 'import_end');
                    }
                    break;
                default:
                    $result = ['success' => false, 'message' => (string)__('Unknown import type.')];
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

    /**
     * Turn an API client batch result into the JSON result for the import widget.
     *
     * A null result means there was nothing valid to send, which is not an error.
     *
     * @param array|null $apiResult ['response' => string, 'code' => int] from Api\Connection::post()
     * @return array
     */
    private function batchResult($apiResult): array
    {
        if ($apiResult === null) {
            return ['success' => true];
        }

        $code = (int)($apiResult['code'] ?? 0);
        if ($code >= 200 && $code < 300) {
            return ['success' => true];
        }

        $this->helper->logError('Metrilo import request failed with HTTP ' . $code . ': '
            . substr(trim((string)($apiResult['response'] ?? '')), 0, 200));

        return ['success' => false, 'message' => $this->describeApiError($code, $apiResult['response'] ?? '')];
    }

    private function describeApiError(int $code, $response): string
    {
        switch ($code) {
            case 401:
                return (string)__('Metrilo rejected the import: the API Token is missing or invalid (HTTP 401). '
                    . 'Check the API Token in Stores > Configuration > Metrilo for this store view.');
            case 402:
                return (string)__('Metrilo rejected the import: your Metrilo project is not accepting data (HTTP 402). '
                    . 'Check your Metrilo subscription.');
            case 403:
                return (string)__('Metrilo rejected the import: the API Secret does not match your Metrilo project '
                    . '(HTTP 403). Check the API Secret in Stores > Configuration > Metrilo for this store view.');
            default:
                $details = substr(trim(strip_tags((string)$response)), 0, 200);

                return (string)__('Metrilo rejected the import (HTTP %1). %2', $code, $details);
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
