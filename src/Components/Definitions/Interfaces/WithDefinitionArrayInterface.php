<?php

declare(strict_types=1);

namespace LWP\Components\Definitions\Interfaces;

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\BasePropertyModel;

interface WithDefinitionArrayInterface
{
    //

    public function getDefinitionDataArray(): array;


    //

    public function getDefinitionCollectionSet(): DefinitionCollectionSet;


    //

    public function getReusableDefinitionCollectionSet(): DefinitionCollectionSet;


    //

    public function getModel(): BasePropertyModel;
}
