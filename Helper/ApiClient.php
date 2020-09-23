<?php

namespace Metrilo\Analytics\Helper;

use \Metrilo\Analytics\Api\Client;

class ApiClient extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Metrilo\Analytics\Helper\Data                $helper,
        \Magento\Framework\App\ProductMetadata        $metaData,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Filesystem\DirectoryList   $dirList
    ) {
        $this->helper       = $helper;
        $this->metaData     = $metaData;
        $this->moduleList   = $moduleList;
        $this->dirList      = $dirList;
    }

    public function getClient($storeId)
    {
        $helperObject  = $this->helper; // for backward compatibility for php ~5.5, ~5.6
        $token         = $this->helper->getApiToken($storeId);
        $secret        = $this->helper->getApiSecret($storeId);
        $platform      = 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion();
        $pluginVersion = $this->moduleList->getOne($helperObject::MODULE_NAME)['setup_version'];
        $apiEndpoint   = $this->helper->getApiEndpoint();
        return new Client($token, $secret, $platform, $pluginVersion, $apiEndpoint, $this->dirList->getPath('log'));
    }
}
