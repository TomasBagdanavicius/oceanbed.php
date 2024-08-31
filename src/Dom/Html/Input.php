<?php

declare(strict_types=1);

namespace LWP\Dom\Html;

use LWP\Dom\Html\Element;

class Input extends Element
{
    private $input;
    private $DOM;


    public function __construct($input, $DOM)
    {

        $this->input = $input;
        $this->DOM = $DOM;

        parent::__construct($input);
    }


    public function getAttribute($name)
    {

        return $this->input->getAttribute($name);
    }
}
