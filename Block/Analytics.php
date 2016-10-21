<?php

namespace Metrilo\Analytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Analytics extends Template {

    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context                                            $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Metrilo\Analytics\Helper\Data                     $helper
     * @param \Metrilo\Analytics\Model\Analytics                 $dataModel
     * @param array                                              $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Model\Analytics $dataModel,
        array $data = []
    ) {
        $this->config = $scopeConfig;
        $this->_helper = $helper;
        $this->dataModel = $dataModel;
        parent::__construct($context, $data);
    }

    /**
     * Get API Token
     *
     * @return bool|null|string
     */
    public function getApiToken() {
        return $this->_helper->getApiToken();
    }

    /**
     * Get events to track them to metrilo js api
     *
     * @return array
     */
    public function getEvents() {
        return $this->dataModel->getEvents();
    }

    /**
     * Render metrilo js if module is enabled
     *
     * @return string
     */
    protected function _toHtml() {
        if(!$this->_helper->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}