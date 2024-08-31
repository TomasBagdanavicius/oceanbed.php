<?php

declare(strict_types=1);

namespace LWP\Components\Model\SharedAmounts;

use LWP\Common\Array\ArrayCollection;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class SharedAmountCollection extends ArrayCollection
{
    public function __construct(
        public readonly string $collection_name,
        public readonly ?string $collection_description = null,
    ) {

        // Allow for valid constraint object classes to be added only.
        parent::__construct(element_filter: function (mixed $element): true {

            if (!($element instanceof AbstractSharedAmount)) {
                throw new \Exception(sprintf(
                    "Collection \"%s\" accepts elements of class \"%s\" only.",
                    self::class,
                    AbstractSharedAmount::class
                ));
            }

            return true;

            // Use element class name as the name identifier in the collection.
        }, obtain_name_filter: function (mixed $element): ?string {

            if (($element instanceof AbstractSharedAmount)) {
                return $element::class;
            }

            return null;

        });
    }


    //

    public static function fromDefinitionCollection(
        string $group_name,
        DefinitionCollection $definition_collection
    ): self {

        if ($definition_collection->getTypeValue() !== 'group') {
            throw new \Exception(
                "Shared amount collection can only be created from a \"group\" typed definition collection."
            );
        }

        $params = [
            $group_name,
        ];

        if ($definition_collection->containsKey('description')) {
            $params[] = $definition_collection->get('description')->getValue();
        }

        $self_instance = new self(...$params);

        foreach ($definition_collection as $definition_name => $definition) {

            if (
                $definition->getCategory() === DefinitionCategoryEnum::SHARED_AMOUNT
                && $definition->canProduceClassObject()
            ) {
                $self_instance->set($definition_name, $definition->produceClassObject());
            }
        }

        return $self_instance;
    }
}
