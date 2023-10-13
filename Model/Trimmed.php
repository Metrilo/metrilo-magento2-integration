<?php

namespace Metrilo\Analytics\Model;

use Magento\Framework\App\Config\Value;

class Trimmed extends Value
{
    public function beforeSave()
    {
        $value = trim($this->getValue());
        $this->setValue($value);

        return parent::beforeSave();
    }
}
