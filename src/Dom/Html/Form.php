<?php

declare(strict_types=1);

namespace LWP\Dom\Html;

use LWP\Dom\Html\Element;
use LWP\Dom\Html\Input;

class Form extends Element
{
    private $form;
    private $DOM;


    public function __construct($form, $DOM)
    {

        $this->form = $form;
        $this->DOM = $DOM;

        parent::__construct($form);
    }


    public function getData()
    {

        return [
            'attrs' => $this->getAttributes(),
            'fields' => $this->getInputElements(),
        ];
    }


    public function getInputElements()
    {

        $input_elems = $this->DOM->getXPath()->query(".//input", $this->form);
        $data = [];

        foreach ($input_elems as $input_elem) {

            $Input = new Input($input_elem, $this->DOM);

            $name = $Input->getAttribute('name');

            if (!empty($name)) {

                $data[$name] = urldecode($Input->getAttribute('value'));
            }
        }

        return $data;
    }


    public function findByFieldNames($to_find)
    {

        $fields = $this->getInputElements();

        $to_find = [
            '__index' => [
                'to_find' => count($to_find),
                'found' => [],
            ],
            'fields' => $to_find,
        ];

        $index = &$to_find['__index'];
        $index_found = &$index['found'];

        foreach ($fields as $field_name => $field_value) {

            foreach ($to_find['fields'] as $find_name => $find) {

                $found = \LWP\Common\String\Str::posMultiple($field_name, $find);
                $found_count = count($found);

                if ($found_count > 0 && (!isset($index_found[$find_name][0]) || $index_found[$find_name][0] < $found_count)) {

                    // first time found
                    if (!isset($index_found[$find_name][0])) {

                        $index_found[$find_name] = [];
                        $index['to_find']--;
                    }

                    $index_found[$find_name][0] = $found_count;
                    $index_found[$find_name][1] = $field_name;
                }
            }
        }

        return $index;
    }
}
