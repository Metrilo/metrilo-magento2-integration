<?php

namespace Metrilo\Analytics\Helper;

class AdminStoreResolver extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Get storeId for the current admin request context
     *
     * @return int
     */
    public function getAdminStoreId()
    {
        return (int) $this->request->getParam('store', 0);
    }
}
