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
     * @param \Metrilo\Analytics\Model\Import                  $import
     * @param \Magento\Framework\App\Request\Http              $request
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
     # TODO: Ask Miro why \Magento\Framework|App\Action|Context won't compile
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Model\Import $import,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helper            = $helper;
        $this->import            = $import;
        $this->request           = $request;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Import orders history by chunks
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
            
            $token             = $this->helper->getApiToken($storeId);
            $platform          = 'Magento ' . $this->helper->metaData->getEdition() . ' ' . $this->helper->metaData->getVersion();
            $pluginVersion     = $this->helper->moduleList->getOne($this->helper::MODULE_NAME)['setup_version'];
            
            $client            = new Client($token, $platform, $pluginVersion);

//            if ($chunkId == 0) {
//                $this->helper->createActivity($storeId, 'import_start');
//            }

            switch ($importType) {
                case 'customers':
                    $client->customerBatch($this->import->customerData->getCustomers($storeId, $chunkId, $this->import::chunkItems));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', json_encode(array('Customers' => $this->import->customerData->getCustomers($storeId, $chunkId, $this->import::chunkItems))));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', $client->customerBatch($this->import->customerData->getCustomers($storeId, $chunkId, $this->import::chunkItems)));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', '--------------------------------------');
                    $result['success'] = 'customerBatch';
                    break;
                case 'categories':
                    $client->categoryBatch($this->import->categoryData->getCategories($storeId, $chunkId, $this->import::chunkItems));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', json_encode(array('Categories' => $this->import->categoryData->getCategories($storeId, $chunkId, $this->import::chunkItems))));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', $client->categoryBatch($this->import->categoryData->getCategories($storeId, $chunkId, $this->import::chunkItems)));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', '--------------------------------------');
                    $result['success'] = 'categoryBatch';
                    break;
                case 'products':
                    $client->productBatch($this->import->productData->getProducts($storeId, $chunkId, $this->import::chunkItems));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', json_encode(array('Products' => $this->import->productData->getProducts($storeId, $chunkId, $this->import::chunkItems))));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', $client->productBatch($this->import->productData->getProducts($storeId, $chunkId, $this->import::chunkItems)));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', '--------------------------------------');
                    $result['success'] = 'productBatch';
                    break;
                case 'orders':
                    $client->orderBatch($this->import->orderData->getOrders($storeId, $chunkId, $this->import::chunkItems));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', json_encode(array('Orders' => $this->import->orderData->getOrders($storeId, $chunkId, $this->import::chunkItems))));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', $client->orderBatch($this->import->orderData->getOrders($storeId, $chunkId, $this->import::chunkItems)));
                    $this->helper->requestLogger(__DIR__ . 'Request.log', '--------------------------------------');
                    $result['success'] = 'orderBatch';
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

