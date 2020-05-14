<?php

namespace Metrilo\Analytics\Model\Events;

class CategoryView
{
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }
    public function callJS()
    {
        return "window.metrilo.viewCategory('" .
            $this->coreRegistry->registry('current_category')->getId() . "');";
    }
}
