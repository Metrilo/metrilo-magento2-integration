<?php

namespace Metrilo\Analytics\Model\Events;

class ProductViewEvent
{
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }

    public function callJS() {
        return "window.metrilo.viewProduct(" . $this->coreRegistry->registry('current_product')->getId() . ");";
    }
}