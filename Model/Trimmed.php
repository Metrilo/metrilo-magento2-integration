<?php

namespace Metrilo\Analytics\Model;

class Trimmed extends \Magento\Framework\App\Config\Value
{
    public function beforeSave() {
        $value = trim($this->getValue());
        $this->setValue($value);

        parent::beforeSave();
    }
}
