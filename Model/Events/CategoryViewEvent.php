<?php

namespace Metrilo\Analytics\Model\Events;

class CategoryViewEvent
{
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
        file_put_contents('CategoryViewEvent.txt', 'Category Event!', FILE_APPEND);
    }
    public function callJS() {
        return "window.metrilo.viewCategory('" . $this->coreRegistry->registry('current_category')->getId() . "');";
    }
}