<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

interface DatasetFieldValueExtenderInterface
{
    //

    public function getOriginalValue(): int;

    //

    public function getForeignObject(): DataServerInterface;
}
