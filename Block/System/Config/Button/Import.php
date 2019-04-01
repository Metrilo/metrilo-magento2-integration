<?php

namespace Metrilo\Analytics\Block\System\Config\Button;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Block for import button in metrilo configuration
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Import extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'system/config/button/import.phtml';

    /**
     * Import constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Metrilo\Analytics\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Model\Import $import,
        \Metrilo\Analytics\Model\CustomerData $customerData,
        \Metrilo\Analytics\Model\CategoryData $categoryData,
        \Metrilo\Analytics\Model\OrderData $orderData,
        array $data = []
    ) {
        $this->helper       = $helper;
        $this->import       = $import;
        $this->customerData = $customerData;
        $this->categoryData = $categoryData;
        $this->orderData    = $orderData;
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
     * @param  AbstractElement $element
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
     * @param  AbstractElement $element
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

    /**
     * Import model
     *
     * @return \Metrilo\Analytics\Model\Import
     */
    public function getImport()
    {
        return $this->import;
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

    /**
     * Get contextual store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return (int) $this->helper->resolver->getAdminStoreId();
    }
}
