<?php

namespace Metrilo\Analytics\Model\Events;

use Magento\Framework\Registry;

class CategoryView
{
    private Registry $coreRegistry;

    public function __construct(
        Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }
    public function callJS(): string
    {
        return "window.metrilo.viewCategory('" .
            $this->coreRegistry->registry('current_category')->getId() . "');";
    }
}
