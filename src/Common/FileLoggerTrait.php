<?php

declare(strict_types=1);

namespace LWP\Common;

trait FileLoggerTrait
{
    // Logs a string message

    public function log(string $message): void
    {

        if ($this->file_logger && is_object($this->file_logger) && $this->file_logger instanceof FileLogger) {
            $this->file_logger->logText($message);
        }
    }
}
