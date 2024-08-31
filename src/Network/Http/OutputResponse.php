<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Network\Http\Enums\ResponseStatusCodesEnum;
use LWP\Network\Headers;

class OutputResponse
{
    public function __construct(
        public readonly ResponseStatusCodesEnum $status_code,
        public readonly Headers $headers,
        public readonly string $content
    ) {

    }


    //

    public function sendHeaders(): void
    {

        foreach ($this->headers as $header_field_name => $value) {

            if (!is_array($value)) {
                header($header_field_name . ': ' . $value);
            } else {
                foreach ($value as $header_field_value) {
                    header($header_field_name . ': ' . $header_field_value, replace: false);
                }
            }
        }
    }


    //

    public function sendContent(): void
    {

        echo $this->content;
    }


    //

    public function send(): never
    {

        http_response_code($this->status_code->value);
        $this->sendHeaders();
        $this->sendContent();

        exit;
    }
}
