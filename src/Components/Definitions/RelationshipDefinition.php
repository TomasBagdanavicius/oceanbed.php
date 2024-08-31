<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class RelationshipDefinition extends Definition
{
    public const DEFINITION_NAME = 'relationship';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::DATASET;
    public const IS_PRIMARY = false;
    public const CAN_PRODUCE_CLASS_OBJECT = false;


    public function __construct(
        string|array $value
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(string $value): void
    {

        $this->value = $value;
    }


    //

    public function getRelationshipName(): string
    {

        return ($this->value['name'] ?? $this->value);
    }
}
