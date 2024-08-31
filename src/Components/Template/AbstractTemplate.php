<?php

declare(strict_types=1);

namespace LWP\Components\Template;

use LWP\Filesystem\Filesystem;
use LWP\Filesystem\Path\FilePath;

abstract class AbstractTemplate
{
    public function __construct()
    {

    }


    //

    abstract public function getVariables(): array;


    //
    // return - void or string

    public function applyToFile(FilePath $file_path, bool $return = true)
    {

        $template = Filesystem::loadFileAsTemplate($file_path, $return, $this->getVariables());

        if ($template !== null) {
            return $template;
        }
    }
}
