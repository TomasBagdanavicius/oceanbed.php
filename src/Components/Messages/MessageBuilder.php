<?php

declare(strict_types=1);

namespace LWP\Components\Messages;

use LWP\Components\Messages\Message;

class MessageBuilder extends Message
{
    public function __construct(
        int $type,
        string $text
    ) {

        $structure = new \stdClass();
        $structure->type = $type;
        $structure->text = $text;

        parent::__construct($structure);
    }


    public function getMessageInstance(): Message
    {

        return new parent($this->getStructure());
    }
}
