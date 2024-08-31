<?php

declare(strict_types=1);

namespace LWP\Components\Model;

trait ModelErrorHandlingTrait
{
    protected int $error_handling_mode = self::COLLECT_ERRORS;


    // Sets preferred error handling mode.
    #todo: use enumeration.

    public function setErrorHandlingMode(int $mode): void
    {

        $this->error_handling_mode = $mode;
    }


    // Gets current error handling mode.

    public function getErrorHandlingMode(): int
    {

        return $this->error_handling_mode;
    }
}
