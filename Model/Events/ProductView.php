<?php

namespace Metrilo\Analytics\Model\Events;

use Magento\Framework\Registry;

class ProductView
{
    private Registry $coreRegistry;

    public function __construct(
        Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }

    public function callJS(): string
    {
        return "window.metrilo.viewProduct(" . $this->coreRegistry->registry('current_product')->getId() . ");";
    }
}
