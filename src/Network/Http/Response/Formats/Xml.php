<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

use LWP\Components\Template\TemplateInterface;
use LWP\Network\Headers;
use LWP\Components\Rules\XmlFormattingRule;

class Xml extends ResponseFormat
{
    public function __construct(
        public readonly Headers $headers,
        public readonly TemplateInterface $template
    ) {

    }


    //

    public function getHeaders(): Headers
    {

        $this->headers->set("content-type", "application/xml");

        return $this->headers;
    }


    //

    public function getContent(): string
    {

        $formatting_rule = new XmlFormattingRule();
        $formatter = $formatting_rule->getFormatter();

        return $formatter->format($this->template->getPayload());
    }
}
