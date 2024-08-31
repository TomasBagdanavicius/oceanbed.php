<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Network\Headers;

class MultiPartMessage extends Message implements \Stringable
{
    public const SUBTYPE_FORM_DATA = 'form-data';
    public const SUBTYPE_MIXED = 'mixed';
    public const SUBTYPE_ALTERNATIVE = 'alternative';
    public const SUBTYPE_PARALLER = 'parallel';
    public const SUBTYPE_DIGEST = 'digest';

    private int $preamble_size = 0;
    private int $epilogue_size = 0;


    public function __construct(
        Headers $headers,
        Boundary $boundary,
        private string $subtype = self::SUBTYPE_MIXED,
        string $preamble = null,
        string $epilogue = null,
    ) {

        parent::__construct($headers, $boundary);

        $this->setPreamble($preamble);
        $this->setEpilogue($epilogue);
    }


    // Gets the message as a string.

    public function __toString(): string
    {

        return ($this->headers->__toString() . "\r\n" . $this->preamble . "\r\n" . $this->body->__toString() . "\r\n" . $this->epilogue);
    }


    // Gets the content size.

    public function getContentSize(): int
    {

        return (parent::getContentSize() + $this->preamble_size + $this->epilogue_size);
    }


    // Sets the preamble.

    public function setPreamble(string $preamble = null): void
    {

        $this->preamble = $preamble;
        $this->preamble_size = (strlen($preamble) + 2);
    }


    // Gets the preamble.

    public function getPreamble(): string
    {

        return $this->preamble;
    }


    // Sets the epilogue.

    public function setEpilogue(string $epilogue = null): void
    {

        $this->epilogue = $epilogue;
        $this->epilogue_size = (strlen($epilogue) + 2);
    }


    // Gets the epilogue.

    public function getEpilogue(): string
    {

        return $this->epilogue;
    }


    // Adds the default "content-type" header field value.

    public function addContentTypeHeaderField(): void
    {

        $this->headers->set('content-type', $this->getContentTypeHeaderFieldValue());
    }


    // Gets header field value for the "content-type" header field.

    public function getContentTypeHeaderFieldValue(): string
    {

        return Boundary::buildContentTypeHeaderFieldValue($this->body->getDelimiter(), $this->subtype);
    }
}
