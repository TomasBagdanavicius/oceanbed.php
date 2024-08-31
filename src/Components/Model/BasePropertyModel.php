<?php

declare(strict_types=1);

namespace LWP\Components\Model;

use LWP\Components\Properties\BasePropertyCollection;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Properties\AbstractPropertyCollection;

class BasePropertyModel extends AbstractModel
{
    public function __construct(
        ?AbstractPropertyCollection $property_collection = null,
    ) {

        parent::__construct($property_collection ?: new BasePropertyCollection());
    }


    //

    public static function createPropertyFromDefinitionCollection(
        string $property_name,
        DefinitionCollection $definition_collection,
        AbstractModel $model
    ): BaseProperty {

        return BaseProperty::fromDefinitionCollection($property_name, $definition_collection);
    }
}
