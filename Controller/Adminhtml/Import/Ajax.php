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
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Metrilo\Analytics\Helper\Data                   $helper
     * @param \Metrilo\Analytics\Model\Import                  $import
     * @param \Magento\Framework\App\Request\Http              $request
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Model\Import $import,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->import = $import;
        $this->request = $request;
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
            $jsonFactory = $this->resultJsonFactory->create();
            $result = ['success' => false];

            $storeId = (int)$this->request->getParam('store_id');
            $chunkId = (int)$this->request->getParam('chunk_id');
            // Get orders from the Database
            $orders = $this->import->getOrders($storeId, $chunkId);
            // Send orders via API helper method
            $this->helper->callBatchApi($storeId, $orders, false);
            $result['success'] = true;
            return $jsonFactory->setData($result);
        } catch (\Exception $e) {
            return $jsonFactory->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}