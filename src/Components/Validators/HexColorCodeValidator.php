<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

use LWP\Common\Exceptions\InvalidValueException;

class HexColorCodeValidator extends Validator
{
    public function __construct(
        string $value
    ) {

        parent::__construct($value);
    }


    //

    public function validate(): bool
    {

        $value = $this->value;
        $value = ltrim($value, '#');

        if (!ctype_xdigit($value) || (strlen($value) !== 6 && strlen($value) !== 3)) {
            throw new InvalidValueException(sprintf("Value \"%s\" is not a valid hex color code", $value));
        }

        return true;
    }
}
