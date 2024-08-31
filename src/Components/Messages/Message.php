<?php

declare(strict_types=1);

namespace LWP\Components\Messages;

use LWP\Common\Indexable;
use LWP\Common\Collectable;

class Message implements Indexable, Collectable, \Stringable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public const MESSAGE_SUCCESS = 1;
    public const MESSAGE_NOTICE = 2;
    public const MESSAGE_WARNING = 3;
    public const MESSAGE_ERROR = 4;


    public function __construct(
        private \stdClass $structure,
    ) {

    }


    //

    public function __get(string $prop)
    {

        return $this->getProp($prop);
    }


    //

    public function __set(string $prop, $value)
    {

        return $this->setProp($prop, $value);
    }


    //

    public function __toString(): string
    {

        return $this->getText();
    }


    //

    public function getProp(string $prop)
    {

        return (isset($this->structure->$prop))
            ? $this->structure->$prop
            : null;
    }


    //

    public function setProp(string $prop, $value)
    {

        return $this->structure->$prop = $value;
    }


    //

    public function getStructure(): \stdClass
    {

        return $this->structure;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'type',
            'text',
            'code',
            'origin',
            'data',
        ];
    }


    //

    public function getIndexableData(): object
    {

        return $this->getStructure();
    }


    //

    public function setType(int $value): void
    {

        $this->setProp('type', $value);
    }


    //

    public function setText(string $value): void
    {

        $this->setProp('text', $value);
    }


    //

    public function setCode(int $value): void
    {

        $this->setProp('code', $value);
    }


    //

    public function setOrigin($origin): void
    {

        $this->setProp('origin', $origin);
    }


    //

    public function setData($value): void
    {

        $this->setProp('data', $value);
    }


    //

    public function isSuccess(): bool
    {

        return ($this->getType() === self::MESSAGE_SUCCESS);
    }


    //

    public function isNotice(): bool
    {

        return ($this->getType() === self::MESSAGE_NOTICE);
    }


    //

    public function isWarning(): bool
    {

        return ($this->getType() === self::MESSAGE_WARNING);
    }


    //

    public function isError(): bool
    {

        return ($this->getType() === self::MESSAGE_ERROR);
    }


    //

    public function getText(): string
    {

        return $this->getProp('text');
    }


    //

    public function getType(): int
    {

        return $this->getProp('type');
    }


    //

    public function getCode(): int
    {

        return $this->getProp('code');
    }


    //

    public function getData()
    {

        return $this->getProp('data');
    }
}
