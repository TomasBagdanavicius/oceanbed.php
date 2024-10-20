<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\Exceptions\FormatError;
use LWP\Dom\Xml;

class XmlFormatter implements FormatterInterface
{
    public function __construct(
        public readonly XmlFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options

    public function format(array|object $value): string
    {

        $result = Xml::dataToSimpleXmlElement(
            $value,
            $this->formatting_rule->getRealTagnameAttrName()
        )->asXML();

        if (!$result) {
            throw new FormatError("Could not convert data to XML");
        }

        return $result;
    }


    //

    public function canFormat(mixed $value): bool
    {

        return is_array($value) || is_object($value);
    }
}
