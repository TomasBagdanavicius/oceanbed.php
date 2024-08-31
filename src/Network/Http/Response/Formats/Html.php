<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

use LWP\Components\Template\TemplateInterface;
use LWP\Filesystem\Path\FilePath;
use LWP\Network\Headers;

class Html extends ResponseFormat
{
    public function __construct(
        public readonly Headers $headers,
        public readonly TemplateInterface $template,
        public readonly FilePath $file_path
    ) {

    }


    //

    public function getHeaders(): Headers
    {

        $this->headers->set("content-type", "text/html; charset=utf-8");

        return $this->headers;
    }


    //

    public function getContent(): string
    {

        return $this->template->applyToFile($this->file_path, return: true);
    }
}
