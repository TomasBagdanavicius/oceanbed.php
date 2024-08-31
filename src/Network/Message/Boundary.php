<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Common\Common;
use LWP\Common\Array\ArrayCollection;
use LWP\Common\String\Str;
use LWP\Network\Message\MultiPartMessage;
use LWP\Network\Headers;

class Boundary implements \Stringable
{
    protected string $delimiter;
    private ArrayCollection $part_collection;
    private int $size = 0;
    private int $encapsulation_boundary_length;


    public function __construct()
    {

        $this->delimiter = self::createDelimiter();
        $this->encapsulation_boundary_length = strlen($this->getEncapsulationBoundary());
        $this->part_collection = new ArrayCollection();
    }


    // Gets the boundary as a string.

    public function __toString(): string
    {

        $result = '';

        foreach ($this->part_collection as $element) {

            $result .= $this->getEncapsulationBoundary();
            $result .= ($element->__toString() . "\r\n");
        }

        $result .= $this->getClosingBoundary();

        return $result;
    }


    //

    public function addContentTypeHeaderField(Headers $headers): void
    {

        $headers->set('content-type', $this->getContentTypeHeaderFieldValue());
    }


    //

    public function getContentTypeHeaderFieldValue(string $subtype = MultiPartMessage::SUBTYPE_MIXED): string
    {

        return self::buildContentTypeHeaderFieldValue($this->delimiter, $subtype);
    }


    //

    public function getEncapsulationBoundaryLength(): int
    {

        return $this->encapsulation_boundary_length;
    }


    // Gets the delimiter.

    public function getDelimiter(): string
    {

        return $this->delimiter;
    }


    // Gets the size.

    public function getSize(): int
    {

        return ($this->size)
            ? ($this->size + strlen($this->getClosingBoundary()))
            : 0;
    }


    // Adds boundary component.

    public function add(mixed $part): ?int
    {

        // To maintain compatability with classes that extend this class.
        if (!($part instanceof BodyPart) && !($part instanceof Boundary)) {
            Common::throwTypeError(1, __FUNCTION__, (__NAMESPACE__ . '\BodyPart or ' . __NAMESPACE__ . 'Boundary'), gettype($part));
        }

        // +2 for the trailing line break.
        $this->size += ($this->encapsulation_boundary_length + $part->getSize() + 2);

        return $this->part_collection->add($part);
    }


    //

    public function remove(int $index): BodyPart|Boundary
    {

        if ($removed = $this->part_collection->remove($index)) {
            $this->size -= ($this->encapsulation_boundary_length + $removed->getSize());
        }

        return $removed;
    }


    // Gets the encapsulation boundary line.

    public function getEncapsulationBoundary(): string
    {

        return ('--' . $this->delimiter . "\r\n");
    }


    // Gets the closing boundary line.

    public function getClosingBoundary(): string
    {

        return ('--' . $this->delimiter . '--');
    }


    // Creates a delimiter string.

    public static function createDelimiter(): string
    {

        // Must be no longer than 70 characters (not counting the two leading hyphens).
        return (str_repeat('-', 27) . Str::random(29, '0-9a-zA-Z'));
    }


    // Builds the "content-length" header field's value.

    public static function buildContentTypeHeaderFieldValue(string $delimiter, string $subtype = MultiPartMessage::SUBTYPE_MIXED): string
    {

        return ('multipart/' . $subtype . '; boundary="' . $delimiter . '"');
    }
}
