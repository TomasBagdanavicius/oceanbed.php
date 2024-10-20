<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

use LWP\Components\Template\TemplateInterface;
use LWP\Components\Rules\SerializeFormattingRule;
use LWP\Network\Headers;

class Serialize extends ResponseFormat
{
    public function __construct(
        public readonly Headers $headers,
        public readonly TemplateInterface $template
    ) {

    }


    //

    public function getHeaders(): Headers
    {

        $this->headers->set("content-type", "text/plain; charset=utf-8");

        return $this->headers;
    }


    //

    public function getContent(): string
    {

        $formatting_rule = new SerializeFormattingRule();
        $formatter = $formatting_rule->getFormatter();

        return $formatter->format($this->template->getPayload());
    }
}
