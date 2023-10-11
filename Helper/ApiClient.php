<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Metrilo\Analytics\Api\ClientFactory;

class ApiClient extends AbstractHelper
{
    private Data $helper;

    private ProductMetadata $metaData;

    private ClientFactory $clientFactory;

    private ComponentRegistrarInterface $componentRegistrar;

    private ReadFactory $readFactory;

    private Json $json;

    public function __construct(
        Data $helper,
        ProductMetadata $metaData,
        ClientFactory $clientFactory,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        Json $json,
        Context $context
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->metaData = $metaData;
        $this->clientFactory = $clientFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->json = $json;
    }

    public function getClient($storeId)
    {
        $token = $this->helper->getApiToken($storeId);
        $secret = $this->helper->getApiSecret($storeId);
        $platform = 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion();
        $apiEndpoint = $this->helper->getApiEndpoint();

        return $this->clientFactory->create(
            [
                'token' => $token,
                'secret' => $secret,
                'platform' => $platform,
                'pluginVersion' => $this->getPluginVersion(),
                'apiEndpoint' => $apiEndpoint,
            ]
        );
    }

    private function getPluginVersion(): ?string
    {
        try {
            $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $this->_getModuleName());
            $read = $this->readFactory->create($modulePath);
            $content = $read->readFile('composer.json');
            $decoded = $this->json->unserialize($content);

            return $decoded['version'] ?? null;
        } catch (LocalizedException) {
            return null;
        }
    }
}
