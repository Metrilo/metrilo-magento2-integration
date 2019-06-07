<?php

namespace Metrilo\Analytics\Model\Events;

class ProductViewEvent
{
    public function __construct(
        $productId
    ) {
        $this->productId = $productId;
    }

    public function callJS() {
        return "window.metrilo.viewProduct(" . $this->productId . ");";
    }
}