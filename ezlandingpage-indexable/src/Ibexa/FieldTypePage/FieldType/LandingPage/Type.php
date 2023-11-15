<?php

namespace App\Ibexa\FieldTypePage\FieldType\LandingPage;


use Ibexa\Core\Base\Exceptions\InvalidArgumentType;

class Type extends \Ibexa\FieldTypePage\FieldType\LandingPage\Type
{
    protected static function checkValueType($value): void
    {
        if (!$value instanceof \Ibexa\FieldTypePage\FieldType\LandingPage\Value) {
            throw new InvalidArgumentType('$value', '\Ibexa\FieldTypePage\FieldType\LandingPage\Value', $value);
        }
    }

    public function isSearchable(): bool
    {
        return true;
    }
}

