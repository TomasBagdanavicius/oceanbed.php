<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Components\DataTypes\DataTypeValidator;
use LWP\Components\Rules\NumberFormattingRule;

class NumberDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value,
        public ?NumberFormattingRule $number_formatting_rule = null,
    ) {

    }


    //

    public function validate(): bool
    {

        if (!is_string($this->value) && !is_int($this->value) && !is_float($this->value)) {
            return false;
        }

        try {
            $parser = new NumberDataTypeParser((string)$this->value, $this->number_formatting_rule);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
