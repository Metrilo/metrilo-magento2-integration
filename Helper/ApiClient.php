<?php

    namespace Metrilo\Analytics\Helper;

    use \Metrilo\Analytics\Api\Client;

    class ApiClient extends \Magento\Framework\App\Helper\AbstractHelper
    {
        protected $client;
        private $token;
        private $platform;
        private $pluginVersion;
    
        public function __construct(
            \Metrilo\Analytics\Helper\Data                $helper,
            \Magento\Store\Model\StoreManagerInterface    $storeManager,
            \Magento\Framework\App\ProductMetadata        $metaData,
            \Magento\Framework\Module\ModuleListInterface $moduleList
        )
        {
            $this->helper       = $helper;
            $this->storeManager = $storeManager;
            $this->metaData     = $metaData;
            $this->moduleList   = $moduleList;
        }
    
        public function getClient($storeId)
        {
            $token         = $this->helper->getApiToken($storeId);
            $platform      = 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion();
            $pluginVersion = $this->moduleList->getOne($this->helper::MODULE_NAME)['setup_version'];
            $apiEndpoint   = ($this->helper->getApiEndpoint($storeId)) ? $this->helper->getApiEndpoint($storeId) : 'http://p.metrilo.com';
            return new Client($token, $platform, $pluginVersion, $apiEndpoint);
        }
    }
