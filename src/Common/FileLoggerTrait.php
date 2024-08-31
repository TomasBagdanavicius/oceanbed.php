<?php

declare(strict_types=1);

namespace LWP\Common;

trait FileLoggerTrait
{
    // Logs a string message

    public function log(string $msg)
    {

        if ($this->file_logger) {
            $this->file_logger->logText($msg);
        }
    }
}
