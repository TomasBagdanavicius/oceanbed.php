<?php

declare(strict_types=1);

namespace LWP\Components\Messages;

use LWP\Common\Common;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Array\RepresentedClassObjectCollection;

class MessageCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct(
            $data,
            element_filter: function (mixed $element): true {

                if (!($element instanceof Message)) {

                    $element_type = gettype($element);

                    Common::throwTypeError(1, __FUNCTION__, Message::class, (($element_type === 'object')
                        ? $element::class
                        : $element_type));
                }

                return true;
            }
        );
    }


    // Creates a new message object instance and attaches it to this collection.

    public function createNewMember(array $params = []): Message
    {

        $message = new Message();
        $message->registerCollection($this, $this->add($message));

        return $message;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }


    // Adds a new message to the collection from a string.

    public function addFromString(int $type, string $text, ?int $code = null): ?int
    {

        $builder = new MessageBuilder($type, $text);

        if ($code !== null) {
            $builder->setCode($code);
        }

        return $this->add($builder->getMessageInstance());
    }


    // Adds a new success type message to the collection from a string.

    public function addSuccessFromString(string $text, ?int $code = null): ?int
    {

        return $this->addFromString(Message::MESSAGE_SUCCESS, $text, $code);
    }


    // Adds a new notice type message to the collection from a string.

    public function addNoticeFromString(string $text, ?int $code = null): ?int
    {

        return $this->addFromString(Message::MESSAGE_NOTICE, $text, $code);
    }


    // Adds a new warning type message to the collection from a string.

    public function addWarningFromString(string $text, ?int $code = null): ?int
    {

        return $this->addFromString(Message::MESSAGE_WARNING, $text, $code);
    }


    // Adds a new error type message to the collection from a string.

    public function addErrorFromString(string $text, ?int $code = null): ?int
    {

        return $this->addFromString(Message::MESSAGE_ERROR, $text, $code);
    }


    // Imports messages to the collection from an array.

    public function importFromArray(array $payload): void
    {

        foreach ($payload as $message_structure) {

            $this->add(
                new Message($message_structure)
            );
        }
    }


    // Tells if there are any success type messages in the collection.

    public function hasSuccessMessages(): bool
    {

        return boolval($this->matchBySingleConditionCount('type', Message::MESSAGE_SUCCESS));
    }


    // Tells if there are any error type messages in the collection.

    public function hasErrors(): bool
    {

        return boolval($this->matchBySingleConditionCount('type', Message::MESSAGE_ERROR));
    }


    // Tells if there are any notice type messages in the collection.

    public function hasNotices(): bool
    {

        return boolval($this->matchBySingleConditionCount('type', Message::MESSAGE_NOTICE));
    }


    // Tells if there are any warning type messages in the collection.

    public function hasWarnings(): bool
    {

        return boolval($this->matchBySingleConditionCount('type', Message::MESSAGE_WARNING));
    }


    // Gives the count number of success type messages in the collection.

    public function successMessagesCount(): int
    {

        return $this->matchBySingleConditionCount('type', Message::MESSAGE_SUCCESS);
    }


    // Gives the count number of error type messages in the collection.

    public function errorsCount(): int
    {

        return $this->matchBySingleConditionCount('type', Message::MESSAGE_ERROR);
    }


    // Gives the count number of notice type messages in the collection.

    public function noticesCount(): int
    {

        return $this->matchBySingleConditionCount('type', Message::MESSAGE_NOTICE);
    }


    // Gives the count number of warning type messages in the collection.

    public function warningsCount(): int
    {

        return $this->matchBySingleConditionCount('type', Message::MESSAGE_WARNING);
    }


    // Creates a separate collection containing success type messages only.

    public function getSuccessMessages(): self
    {

        return $this->matchBySingleCondition('type', Message::MESSAGE_SUCCESS);
    }


    // Creates a separate collection containing notice type messages only.

    public function getNotices(): self
    {

        return $this->matchBySingleCondition('type', Message::MESSAGE_NOTICE);
    }


    // Creates a separate collection containing warning type messages only.

    public function getWarnings(): self
    {

        return $this->matchBySingleCondition('type', Message::MESSAGE_WARNING);
    }


    // Creates a separate collection containing error type messages only.

    public function getErrors(): self
    {

        return $this->matchBySingleCondition('type', Message::MESSAGE_ERROR);
    }
}
