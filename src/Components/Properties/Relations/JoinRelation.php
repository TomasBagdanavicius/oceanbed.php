<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Relations;

use LWP\Components\Properties\EnhancedProperty;
use LWP\Common\OptionManager;
use LWP\Components\DataTypes\ValueOriginEnum;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Components\Properties\EnhancedPropertyCollection;
use LWP\Components\Properties\RelationalPropertyCollection;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Components\Rules\ConcatFormattingRule;
use LWP\Components\Rules\FormattingRuleCollection;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Common\String\Format;

class JoinRelation
{
    public readonly OptionManager $options;


    public function __construct(
        // Property which is dependant upon the related properties.
        private EnhancedProperty $primary_property,
        // Properties, which when combined, will form the primary property value.
        private EnhancedPropertyCollection|RelationalPropertyCollection $related_properties,
        array $options = []
    ) {

        $this->options = new OptionManager($options, [
            'separator' => ' ',
            'shrink' => false,
            'shrink_order' => null
        ], [
            'separator',
            'shrink',
            'shrink_order'
        ]);

        $primary_property->onAfterSetValue(
            $this->onPrimaryPropertySetValue(...),
            identifier: 'join_prime_after_set'
        );

        $primary_property->onUnsetValue(
            $this->onPrimaryPropertyUnsetValue(...),
            identifier: 'join_prime_unset'
        );

        foreach ($this->related_properties as $name => $related_property) {

            $related_property->onAfterSetValue(
                $this->onRelatedPropertySetValue(...),
                /* Primary property name suffix is required to create a unique identifier, because one related property can be tied to multiple prime properties. */
                identifier: ('join_rel_after_set_' . $primary_property->property_name)
            );
        }
    }


    // Gets the primary property object.

    public function getPrimaryProperty(): EnhancedProperty
    {

        return $this->primary_property;
    }


    // Gets the related property collection object.

    public function getRelatedProperties(): EnhancedPropertyCollection|RelationalPropertyCollection
    {

        return $this->related_properties;
    }


    //

    private function onPrimaryPropertySetValue(mixed $value): mixed
    {

        if ($value === null) {
            return $value;
        }

        $is_from_main_scope = ($this->primary_property->getValueOrigin() !== ValueOriginEnum::INTERNAL);

        if (!$is_from_main_scope) {
            return $value;
        }

        $count_related_properties = $this->related_properties->count();
        $value_parts = explode($this->options->separator, (string)$value, $count_related_properties);
        $is_shrink = $this->options->shrink;
        $count_value_parts = count($value_parts);

        if (!$is_shrink) {

            if ($count_value_parts !== $count_related_properties) {

                throw new \LengthException(sprintf(
                    "Provided value (%s) consists of %d %s, %d expected",
                    $value,
                    $count_value_parts,
                    Format::getCasualSingularOrPlural($count_value_parts, "element"),
                    $count_related_properties
                ));
            }
        }

        $order = null;

        if ($is_shrink) {

            $shrink_order = $this->options->shrink_order;

            if (isset($shrink_order[$count_value_parts])) {
                $order = $this->options->shrink_order[$count_value_parts];
            }
        }

        $i = 0;

        foreach ($this->related_properties as $related_property) {

            $key = $i;

            if ($order) {
                $key = array_search($related_property->property_name, $order, strict: true);
            }

            if ($key !== false && isset($value_parts[$key])) {
                $related_property->setValue($value_parts[$key], value_origin: ValueOriginEnum::INTERNAL);
            }

            $i++;
        }

        return $value;
    }


    //

    private function onPrimaryPropertyUnsetValue(EnhancedProperty $property): void
    {

        foreach ($this->related_properties as $related_property) {

            $related_property->unsetValue();
        }
    }


    //

    private function recalculatePrimaryBasedOnRelated(): void
    {

        $parts = [];
        $is_shrink = $this->options->shrink;
        $is_finished = true;

        foreach ($this->related_properties as $related_property) {

            try {

                $value = $related_property->getValue();

                if ($value !== null) {
                    $parts[$related_property->property_name] = $value;
                } else {
                    throw new PropertyValueNotAvailableException();
                }

            } catch (PropertyValueNotAvailableException) {

                // No shrinking, any value missing - it's incomplete
                if (!$is_shrink) {
                    $is_finished = false;
                    break;
                }
            }
        }

        if ($is_shrink && !$parts) {

            // Shrinking all the way to an empty set is not permitted
            $is_finished = false;
        }

        if ($is_finished) {

            $value = implode($this->options->separator, $parts);

            // Set value as value container with descriptor to indicate that it's a concatenated value (hence the formatting rule)
            #review: when value is shrunk (eg. to a single part), does it make sense to have the concat formatting rule added
            $data_type_descriptor_class_name = $this->primary_property->data_type_descriptor_class_name;
            $formatting_rule_collection = new FormattingRuleCollection();
            $formatting_rule_collection->add($this->getConcatFormattingRule());
            $value_descriptor_class_name = $data_type_descriptor_class_name::getValueDescriptorClassName();
            $value_descriptor = new $value_descriptor_class_name(ValidityEnum::UNDETERMINED, $formatting_rule_collection, ValueOriginEnum::INTERNAL);
            $convert_params = [
                $value,
                $value_descriptor
            ];
            $convert_formatting_rule = $this->primary_property->getDataTypeConvertFormattingRule();

            if ($convert_formatting_rule) {
                $convert_params['formatting_rule'] = $convert_formatting_rule;
                // Forces format to be validated inside value container
                $convert_params['add_validity'] = false;
            }

            try {

                /* The reason why convertion is conducted is to (1) accomodate concat formatting rule into the descriptor, (2) verify whether shrunken value is valid */
                $value_container = $this->primary_property->data_type_converter::convert(...$convert_params);

                $this->primary_property->setValue($value_container, ValueOriginEnum::INTERNAL);

            } catch (DataTypeConversionException|DataTypeError) {

                // Continue: shrunken value is not yet good enough to be set into the primary property
            }
        }
    }


    //

    private function onRelatedPropertySetValue(mixed $value, EnhancedProperty $property): mixed
    {

        $this->recalculatePrimaryBasedOnRelated();

        return $value;
    }


    //

    public function getConcatFormattingRule(): ConcatFormattingRule
    {

        return new ConcatFormattingRule($this->options->toArray());
    }


    //

    public static function removeAssociatedPrimaryPropertyCallbacks(EnhancedProperty $primary_property): void
    {

        $primary_property->unsetOnAfterSetValueCallback('join_prime_after_set');
        $primary_property->unsetOnUnsetValueCallback('join_prime_unset');
    }


    //

    public static function removeAssociatedRelatedPropertyCallbacks(EnhancedProperty $related_property, EnhancedProperty $primary_property): void
    {

        $related_property->unsetOnAfterSetValueCallback('join_rel_after_set_' . $primary_property->property_name);
    }
}
