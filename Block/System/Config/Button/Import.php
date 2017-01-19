<?php

namespace Metrilo\Analytics\Block\System\Config\Button;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Import extends \Magento\Config\Block\System\Config\Form\Field
{
	/**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'system/config/button/import.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Metrilo\Analytics\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
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
            $this->setTemplate(static::CHECK_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        // $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get import instance
     *
     * @return boolean
     */
    public function buttonEnabled()
    {
        $helper = $this->helper;
        $storeId = $helper->getStoreId();
         return $helper->isEnabled($storeId) &&
            $helper->getApiToken($storeId) && $helper->getApiSecret($storeId);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
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
}