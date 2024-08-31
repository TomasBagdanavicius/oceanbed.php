<?php

declare(strict_types=1);

namespace LWP\Components\Model\SharedAmounts;

use LWP\Components\Definitions\Definition;

abstract class AbstractSharedAmount
{
    // Creates a new shared amount from a given definition object.

    public static function fromDefinition(Definition $definition): static
    {

        if ($definition->getName() !== static::REPRESENTED_DEFINITION_NAME) {
            throw new \Exception(sprintf("Cannot create %s shared amount from %s.", static::class, $definition::class));
        }

        return new static($definition->getValue());
    }
}
