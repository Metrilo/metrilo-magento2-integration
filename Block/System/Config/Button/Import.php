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
        array $data = []
    ) {
        $this->helper = $helper;
        $this->import = $import;
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
     * Check if button is enabled
     *
     * @return boolean
     */
    public function buttonEnabled()
    {
        $storeId = $this->helper->getStoreId();
         return $this->helper->isEnabled($storeId)
             && $this->helper->getApiToken($storeId)
             && $this->helper->getApiSecret($storeId);
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
}
