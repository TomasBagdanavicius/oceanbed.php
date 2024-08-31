<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Common\Collectable;
use LWP\Common\Indexable;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

abstract class Definition implements Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public function __construct(
        protected mixed $value,
    ) {

    }


    // Gets the value.

    public function getValue(): mixed
    {

        return $this->value;
    }


    // Gets single-member array representation.

    public function getArray(): array
    {

        return [
            static::DEFINITION_NAME => $this->value,
        ];
    }


    // Gets the definition name.

    public function getName(): string
    {

        return static::DEFINITION_NAME;
    }


    // Gets the definition category.

    public function getCategory(): DefinitionCategoryEnum
    {

        return static::DEFINITION_CATEGORY;
    }


    // Gets the definition category name.

    public function getCategoryName(): string
    {

        return static::DEFINITION_CATEGORY->value;
    }


    // Tells if this definition is primary. A valid definition collection must contain at least one primary definition.

    public function isPrimary(): bool
    {

        return static::IS_PRIMARY;
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'name',
            'category',
            'is_primary',
        ];
    }


    // Returns value of a given indexable property

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        $this->assertIndexablePropertyExistence($property_name);

        return match ($property_name) {
            'name' => static::DEFINITION_NAME,
            'category' => $this->getCategoryName(),
            'is_primary' => static::IS_PRIMARY,
        };
    }


    // Tells if this definition can produce a relevant class object.

    public function canProduceClassObject(): bool
    {

        return (
            defined(static::class . '::CAN_PRODUCE_CLASS_OBJECT')
            && static::CAN_PRODUCE_CLASS_OBJECT === true
        );
    }
}
