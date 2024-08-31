<?php

declare(strict_types=1);

namespace LWP\Dom\Html;

class Element
{
    private $elem;


    public function __construct($elem)
    {

        $this->elem = $elem;
    }


    //

    public function getAttributes()
    {

        $data = [];

        if ($this->elem->hasAttributes()) {

            foreach ($this->elem->attributes as $attr) {

                $data[$attr->nodeName] = $attr->nodeValue;
            }
        }

        return $data;
    }
}
