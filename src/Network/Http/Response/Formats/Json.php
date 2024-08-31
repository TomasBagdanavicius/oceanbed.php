<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

use LWP\Components\Template\TemplateInterface;
use LWP\Components\Rules\JsonFormattingRule;
use LWP\Network\Headers;

class Json extends ResponseFormat
{
    public function __construct(
        public readonly Headers $headers,
        public readonly TemplateInterface $template
    ) {

    }


    //

    public function getHeaders(): Headers
    {

        $this->headers->set('content-type', 'application/json; charset=utf-8');

        return $this->headers;
    }


    //

    public function getContent(): string
    {

        $formatting_rule = new JsonFormattingRule();
        $formatter = $formatting_rule->getFormatter();

        return $formatter->format($this->template->getPayload());
    }
}
