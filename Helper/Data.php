<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    public const CHUNK_ITEMS = 50;

    public const MODULE_NAME = 'Metrilo_Analytics';

    private StoreManagerInterface $storeManager;

    private LoggerInterface $logger;

    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    public function isEnabled($storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'metrilo_analytics/general/enable',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiToken($storeId)
    {
        return $this->scopeConfig->getValue(
            'metrilo_analytics/general/api_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiSecret($storeId)
    {
        return $this->scopeConfig->getValue(
            'metrilo_analytics/general/api_secret',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiEndpoint()
    {
        $apiEndpoint = $this->scopeConfig->getValue(
            'metrilo_analytics/general/api_endpoint'
        );

        return $apiEndpoint ?? 'https://trk.mtrl.me';
    }

    public function getActivityEndpoint()
    {
        $activityEndpoint = $this->scopeConfig->getValue(
            'metrilo_analytics/general/activity_endpoint'
        );

        return $activityEndpoint ?? 'https://p.metrilo.com';
    }

    public function logError($exception)
    {
        $this->logger->error($exception);
    }

    public function getStoreIdsPerProject($storeIds)
    {
        $storeIdConfigMap = [];
        foreach ($storeIds as $storeId) {
            if ($storeId == 0 || !$this->isEnabled($storeId)) { // store 0 is always admin
                continue;
            }

            $storeIdConfigMap[$storeId] = $this->scopeConfig
                ->getValue(
                    'metrilo_analytics/general/api_key',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
        }
        $storeIdConfigMap = array_unique($storeIdConfigMap);

        return array_keys($storeIdConfigMap);
    }
}
