<?php

namespace Metrilo\Analytics\Block\System\Config\Button;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Model\CategoryData;
use Metrilo\Analytics\Model\CustomerData;
use Metrilo\Analytics\Model\OrderData;
use Metrilo\Analytics\Model\ProductData;

/**
 * Block for import button in metrilo configuration
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Import extends Field
{
    /**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'system/config/button/import.phtml';

    private Data $helper;

    private CustomerData $customerData;

    private CategoryData $categoryData;

    private ProductData $productData;

    private OrderData $orderData;

    /**
     * @param Context $context
     * @param Data $helper
     * @param CustomerData $customerData
     * @param CategoryData $categoryData
     * @param ProductData $productData
     * @param OrderData $orderData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        CustomerData $customerData,
        CategoryData $categoryData,
        ProductData $productData,
        OrderData $orderData,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->customerData = $customerData;
        $this->categoryData = $categoryData;
        $this->productData = $productData;
        $this->orderData = $orderData;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(self::CHECK_TEMPLATE);
        }

        return $this;
    }

    /**
     * Render button and remove scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get block custom template html for the button
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'intern_url' => $this->getUrl($originalData['button_url']),
                'html_id' => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Check if button is enabled
     *
     * @return boolean
     */
    public function buttonEnabled()
    {
        $storeId = $this->getStoreId();

        return $this->helper->isEnabled($storeId)
            && $this->helper->getApiToken($storeId)
            && $this->helper->getApiSecret($storeId);
    }

    /**
     * Generate URL for AJAX import controller
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('metrilo/import/ajax');
    }

    public function getOrderChunks()
    {
        return $this->orderData->getOrderChunks($this->getStoreId());
    }

    public function getCustomerChunks()
    {
        return $this->customerData->getCustomerChunks($this->getStoreId());
    }

    public function getCategoryChunks()
    {
        return $this->categoryData->getCategoryChunks($this->getStoreId());
    }

    public function getProductChunks()
    {
        return $this->productData->getProductChunks($this->getStoreId());
    }

    /**
     * Get contextual store id
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return (int)$this->_request->getParam('store', 0);
    }
}
