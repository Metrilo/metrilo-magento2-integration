<?php

namespace Metrilo\Analytics\Block;

/**
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Head extends \Magento\Framework\View\Element\Template
{
    /**
     * key in session storage
     */
    const DATA_TAG = "metrilo_events";

    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    public $helper;

    /**
     * Dependency injection
     *
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param array                                              $data
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Metrilo\Analytics\Helper\Data                     $helper
     * @param \Magento\Customer\Model\Session                    $customerSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession
        )
    {
        $this->config = $scopeConfig;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Metrilo helper instance
     *
     * @return \Metrilo\Analytics\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get events to track them to metrilo js api
     *
     * @return array
     */
    public function getEvents()
    {
        $events = (array)$this->customerSession->getData(self::DATA_TAG);
        // clear events from session ater get events once
        $this->customerSession->setData(self::DATA_TAG,'');
        return array_filter($events);
    }

    /**
     * Render metrilo js if module is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if($this->helper->isEnabled())
            return $html;
    }
}