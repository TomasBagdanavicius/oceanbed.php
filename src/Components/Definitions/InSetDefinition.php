<?php

declare(strict_types=1);

namespace LWP\Components\Definitions;

use LWP\Components\Constraints\InSetConstraint;
use LWP\Components\Definitions\Enums\DefinitionCategoryEnum;

class InSetDefinition extends Definition
{
    public const DEFINITION_NAME = 'in_set';
    public const DEFINITION_CATEGORY = DefinitionCategoryEnum::CONSTRAINT;
    public const IS_PRIMARY = true;
    public const CAN_PRODUCE_CLASS_OBJECT = true;


    public function __construct(
        array $value,
    ) {

        parent::__construct($value);
    }


    //

    public function setValue(array $value): void
    {

        $this->value = $value;
    }


    //

    public static function getClassObjectClassName(): string
    {

        return InSetConstraint::class;
    }


    //

    public function produceClassObject(): InSetConstraint
    {

        $class_name = self::getClassObjectClassName();
        $params = [];

        if (isset($this->value['set']) && is_array($this->value['set'])) {
            $params['set'] = $this->value['set'];
            if (isset($this->value['use_keys']) && is_bool($this->value['use_keys'])) {
                $params['use_keys'] = $this->value['use_keys'];
            }
        } else {
            $params[] = $this->value;
        }

        return new $class_name(...$params);
    }
}
