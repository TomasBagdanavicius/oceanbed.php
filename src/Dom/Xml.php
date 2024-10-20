<?php

declare(strict_types=1);

namespace LWP\Dom;

use LWP\Common\String\Str;

class Xml
{
    //

    public static function dataToSimpleXmlElement(array|object $data, string $real_tagname_attr_name = 'real_tag_name'): \SimpleXMLElement
    {

        $xml_data = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
        $to_xml = function (array $data, \SimpleXMLElement $xml_data) use (&$to_xml, $real_tagname_attr_name): void {

            foreach ($data as $key => $value) {

                $key_contains_spec_chars = false;
                $key_original = $key;

                // Replace numeric key with "item", since XML does not accept numeric tags
                if (is_numeric($key)) {
                    $key = 'item';
                } elseif (strlen($key) > 100 || Str::posMultiple($key, ['/', '\\', '&', '>', '<'])) {
                    $key = 'item';
                    $key_contains_spec_chars = true;
                }

                if (is_array($value) || is_object($value)) {

                    $node = $xml_data->addChild("$key");
                    $to_xml($value, $node);

                } else {

                    $node = $xml_data->addChild("$key", htmlspecialchars("$value"));
                }

                // When key contains special characters, its value will be copied into a special "real_tag_name" attribute
                if ($key_contains_spec_chars) {
                    $node->addAttribute($real_tagname_attr_name, htmlspecialchars($key_original));
                }
            }
        };

        $to_xml($data, $xml_data);

        return $xml_data;
    }
}
