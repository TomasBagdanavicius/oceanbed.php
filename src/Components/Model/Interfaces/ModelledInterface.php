<?php

declare(strict_types=1);

namespace LWP\Components\Model\Interfaces;

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\BasePropertyModel;

class ModelledInterface
{
    //

    public function getDefinitionDataArray(): array;


    //

    public function getDefinitionCollectionSet(): DefinitionCollectionSet;


    //

    public function getModel(): BasePropertyModel;
}
