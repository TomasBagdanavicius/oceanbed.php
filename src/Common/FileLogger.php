<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Components\Messages\Message;

class FileLogger
{
    public function __construct(
        public readonly string $filepath
    ) {

    }


    //

    public function logText(string $text): void
    {

        $this->write($text);
    }


    //

    public function logMessage(Message $message): void
    {

        $this->write($message->text);
    }


    //

    public function logThrowable(\Throwable $error): void
    {

        $this->write($error->__toString());
    }


    //

    protected function write(string $text): void
    {

        $contents = ('[' . date('Y-m-d H:i:s') . '] ' . $text . "\n");

        file_put_contents($this->filepath, $contents, FILE_APPEND);
    }
}
