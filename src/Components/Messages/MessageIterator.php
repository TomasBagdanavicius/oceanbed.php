<?php

declare(strict_types=1);

namespace LWP\Components\Messages;

class MessageIterator extends \IteratorIterator
{
    public function current(): Message
    {

        return new Message($this->data[$this->position]);
    }
}
