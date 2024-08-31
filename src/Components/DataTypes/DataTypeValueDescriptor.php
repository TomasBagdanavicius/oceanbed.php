<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

use LWP\Common\String\Str;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\Rules\FormattingRuleCollection;
use LWP\Components\Rules\FormattingRule;

class DataTypeValueDescriptor
{
    protected FormattingRuleCollection $formatting_rule_collection;
    public readonly string $data_type_class_name;


    public function __construct(
        public ValidityEnum $validity,
        ?FormattingRuleCollection $formatting_rule_collection = null,
        public readonly ?ValueOriginEnum $value_origin = null
    ) {

        $this->formatting_rule_collection = ($formatting_rule_collection ?? new FormattingRuleCollection());
        $this->data_type_class_name = self::getDescriptorClassName();
    }


    // Gets data type descriptor's fully qualified class name.

    public static function getDescriptorClassName(): string
    {

        return Str::rtrimSubstring(static::class, 'ValueDescriptor');
    }


    //

    public function addFormattingRule(FormattingRule $formatting_rule): void
    {

        if (!$this->data_type_class_name::isSupportedFormattingRule($formatting_rule::class)) {

            throw new \Exception(sprintf(
                "Formatting rule %s is not supported by %s.",
                $formatting_rule::class,
                static::class
            ));
        }

        $this->formatting_rule_collection->add($formatting_rule);
    }


    //

    public function addFormattingRulesIfNotMatching(FormattingRuleCollection $collection): void
    {

        foreach ($collection as $formatting_rule) {

            if (!$this->hasMatchingFormattingRule($formatting_rule)) {
                $this->formatting_rule_collection->add($formatting_rule);
            }
        }
    }


    //

    public function removeFormattingRule(string $formatting_rule_class_name): void
    {

        $this->formatting_rule_collection->remove($formatting_rule_class_name);
    }


    //

    public function getFormattingRuleCollection(): FormattingRuleCollection
    {

        return $this->formatting_rule_collection;
    }


    //

    public function hasMatchingTypeFormattingRule(string $formatting_rule_class_name): bool
    {

        return $this->formatting_rule_collection->containsKey($formatting_rule_class_name);
    }


    //

    public function hasMatchingFormattingRule(FormattingRule $formatting_rule): bool
    {

        if (!$this->hasMatchingTypeFormattingRule($formatting_rule::class)) {
            return false;
        }

        return $this->formatting_rule_collection->get($formatting_rule::class)->matches($formatting_rule);
    }
}
