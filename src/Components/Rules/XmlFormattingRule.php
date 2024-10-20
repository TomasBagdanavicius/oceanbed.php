<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class XmlFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = []
    ) {

        parent::__construct($options);
    }


    // Gets default value for each option

    public static function getOptionDefaultValues(): array
    {

        return [
            'real_tagname_attr_name' => 'real_tag_name',
        ];
    }


    // Gets supported option list.

    public static function getSupportedOptions(): array
    {

        return [
            'real_tagname_attr_name',
        ];
    }


    // Gets option definitions

    public static function getOptionDefinitions(): array
    {

        return [
            'real_tagname_attr_name' => [
                'type' => 'string',
                'min' => 1,
                'max' => 100,
                'allow_empty' => false,
                'description' => "Specifies the name of the XML attribute that stores the original key when it contains special characters or exceeds length limits and cannot be used as XML tag name.",
            ],
        ];
    }


    // Returns the value of "real_tagname_attr_name" option

    public function getRealTagnameAttrName(): string
    {

        return $this->options->real_tagname_attr_name;
    }
}
