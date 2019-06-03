<?php

namespace Metrilo\Analytics\Helper\Events;

class ProductViewEvent extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->_coreRegistry = $registry;
    }
    public function callJS() {
        $product = $this->_coreRegistry->registry('current_product');
        return "window.metrilo.viewProduct(" . $product->getId() . ");";
    }
}