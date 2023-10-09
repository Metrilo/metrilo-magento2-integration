<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleListInterface;
use Metrilo\Analytics\Api\ClientFactory;

class ApiClient extends AbstractHelper
{
    private Data $helper;

    private ModuleListInterface $moduleList;

    private ProductMetadata $metaData;

    private ClientFactory $clientFactory;

    public function __construct(
        Data $helper,
        ProductMetadata        $metaData,
        ModuleListInterface $moduleList,
        ClientFactory $clientFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->helper       = $helper;
        $this->metaData     = $metaData;
        $this->moduleList   = $moduleList;
        $this->clientFactory = $clientFactory;
    }

    public function getClient($storeId)
    {
        $helperObject  = $this->helper; // for backward compatibility for php ~5.5, ~5.6
        $token         = $this->helper->getApiToken($storeId);
        $secret        = $this->helper->getApiSecret($storeId);
        $platform      = 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion();
        $pluginVersion = $this->moduleList->getOne($helperObject::MODULE_NAME)['setup_version'];
        $apiEndpoint   = $this->helper->getApiEndpoint();

        return $this->clientFactory->create(
            [
                'token' => $token,
                'secret' => $secret,
                'platform' => $platform,
                'pluginVersion' => $pluginVersion,
                'apiEndpoint' => $apiEndpoint,
            ]
        );
    }
}
