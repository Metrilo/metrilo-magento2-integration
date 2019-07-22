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
            \Magento\Framework\Module\ModuleListInterface $moduleList,
            \Magento\Framework\Filesystem\DirectoryList   $dirList
        )
        {
            $this->helper       = $helper;
            $this->storeManager = $storeManager;
            $this->metaData     = $metaData;
            $this->moduleList   = $moduleList;
            $this->dirList      = $dirList;
        }
    
        public function getClient($storeId)
        {
            $token         = $this->helper->getApiToken($storeId);
            $platform      = 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion();
            $pluginVersion = $this->moduleList->getOne($this->helper::MODULE_NAME)['setup_version'];
            $apiEndpoint   = $this->helper->getApiEndpoint();
            return new Client($token, $platform, $pluginVersion, $apiEndpoint, $this->dirList->getPath('log'));
        }
    }
