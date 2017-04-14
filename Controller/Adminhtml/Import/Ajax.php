<?php

namespace Metrilo\Analytics\Controller\Adminhtml\Import;

/**
 * @author Miroslav Petrov
 */
class Ajax extends \Magento\Backend\App\Action
{
    /**
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Import orders history by chunks
     *
     * @return string
     */
    public function execute()
    {
        $jsonFactory = $this->resultJsonFactory->create();
        $error = false;
        return $jsonFactory->setData([
            'error' => $error
        ]);
    }
}