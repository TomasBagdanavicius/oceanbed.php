<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

use LWP\Network\Headers;
use LWP\Network\Http\Enums\ResponseStatusCodesEnum;
use LWP\Network\Http\OutputResponse;

abstract class ResponseFormat
{
    //

    abstract public function getHeaders(): Headers;



    //

    abstract public function getContent(): string;


    //

    public function sendAs(ResponseStatusCodesEnum $code): never
    {

        $output_response = new OutputResponse($code, $this->getHeaders(), $this->getContent());
        $output_response->send();
    }
}
