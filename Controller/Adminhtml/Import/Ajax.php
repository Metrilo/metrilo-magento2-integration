<?php

namespace Metrilo\Analytics\Controller\Adminhtml\Import;

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
        \Metrilo\Analytics\Model\CustomerData $customerData,
        \Metrilo\Analytics\Model\CategoryData $categoryData,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helper            = $helper;
        $this->import            = $import;
        $this->customerData      = $customerData;
        $this->categoryData      = $categoryData;
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
        echo $this->category->getCategories(); // GET CATEGORY DATA
        exit;

        try {
            $jsonFactory = $this->resultJsonFactory->create();
            $result = ['success' => false];

            $storeId = (int)$this->request->getParam('storeId');
            $chunkId = (int)$this->request->getParam('chunkId');
            $totalChunks = (int)$this->request->getParam('totalChunks');

            if ($chunkId == 0) {
                $this->helper->createActivity($storeId, 'import_start');
            }

            // Get orders from the Database
            $orders = $this->import->getOrders($storeId, $chunkId);
            // Send orders via API helper method
            $this->helper->callBatchApi($storeId, $orders, false);
            $result['success'] = true;

            if ($chunkId == $totalChunks - 1) {
                $this->helper->createActivity($storeId, 'import_end');
            }

            return $jsonFactory->setData($result);
        } catch (\Exception $e) {
            return $jsonFactory->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
