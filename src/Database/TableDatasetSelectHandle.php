<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetSelectHandle;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Database\SyntaxBuilders\Exceptions\UnsupportedFormattingRuleConfigException;
use LWP\Components\Rules\Exceptions\FormatNegotiationException;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;
use LWP\Database\SyntaxBuilders\ConcatSyntaxBuilder;
use LWP\Components\Rules\FormattingRuleCollection;

class TableDatasetSelectHandle extends AbstractDatasetSelectHandle
{
    private array $select_expression_metadata_cache = [];


    public function __construct(
        DatasetInterface $dataset,
        array $identifiers,
        array $modifiers = [],
        ?string $model_class_name = null,
        array $model_class_extras = []
    ) {

        parent::__construct($dataset, $identifiers, $modifiers, $model_class_name, $model_class_extras);
    }


    //

    public function getDataServerContextClassName(): string
    {

        return TableDatasetDataServerContext::class;
    }


    // Builds select expression metadata for a given property.

    public function buildSelectExpressionMetadataForProperty(BaseProperty $property): ?array
    {

        $property_name = $property->property_name;
        $column_string = $property_name;
        $abbreviation = $this->dataset->getAbbreviation();
        $use_alias_name = false;
        $has_extrinsic_container = $this->hasExtrinsicContainer($property_name);

        if ($has_extrinsic_container) {

            $abbreviation = $this->getTheOtherDatasetForExtrinsicContainer($property_name)->getAbbreviation();
            $column_string = $this->getPropertyNameForExtrinsicContainer($property_name);
            $use_alias_name = true;

        } elseif ($this->dataset->containers->isVirtualContainer($property_name)) {

            $schema = $this->dataset->containers->getDefinitionsForContainer($property_name);

            if (isset($schema['alias'])) {
                $column_string = $schema['alias'];
                $use_alias_name = true;
                // No alias exists for a virtual property, therefore bail out
            } elseif (!isset($schema['join'])) {
                return null;
            }
        }

        // Support for nested formatting rules
        $formatting_rules_applied_count = 0;
        $applied_formatting_rules = null;
        $select_expression_metadata = [
            'column' => $column_string,
            'table_reference' => $abbreviation
        ];

        if ($property->hasFormattingRuleCollection()) {

            $formatting_rules = $property->getFormattingRuleCollection();
            $descriptor = $this->dataset->database->getDescriptor();

            foreach ($formatting_rules as $formatting_rule) {

                if ($descriptor->isSupportedFormattingRule($formatting_rule::class)) {

                    try {
                        $syntax_builder = new ($descriptor->getSyntaxBuilderClassName($formatting_rule::class))($formatting_rule);
                        // Tells if config is supported.
                    } catch (UnsupportedFormattingRuleConfigException) {
                        // Counts as not supported.
                        continue;
                    }

                    $syntax_builder_value = $column_string;

                    if ($syntax_builder instanceof ConcatSyntaxBuilder) {
                        $schema = (!$has_extrinsic_container)
                            ? $this->dataset->containers->getDefinitionsForContainer($property_name)
                            : $this->dataset->foreign_container_collection->get($property_name)->getSchema();
                        $syntax_builder_value = $schema['join']['properties'];
                    }

                    try {

                        $column_string = $syntax_builder->getFunctionSyntax(
                            $syntax_builder_value,
                            (($formatting_rules_applied_count === 0)
                                ? FormatSyntaxBuilderValueEnum::COLUMN
                                : FormatSyntaxBuilderValueEnum::RAW),
                            $abbreviation // Works with "COLUMN" only.
                        );

                        // Could not negotiate format, eg. desired date-time format could not be converted to a substitute in SQL supported syntax.
                    } catch (FormatNegotiationException) {
                        // Counts as not supported.
                        continue;
                    }

                    /* Formatting rule has been applied. */
                    $applied_formatting_rules ??= new FormattingRuleCollection();
                    $applied_formatting_rules->add($formatting_rule);

                    $formatting_rules_applied_count++;
                }
            }
        }

        if ($formatting_rules_applied_count) {
            $select_expression_metadata['expression'] = $column_string;
        }

        // Alias component
        if ($formatting_rules_applied_count || $use_alias_name) {
            $select_expression_metadata['alias_name'] = $property_name;
        }

        $cache_identifier = ($select_expression_metadata['alias_name'] ?? $select_expression_metadata['column']);
        $this->select_expression_metadata_cache[$cache_identifier] = $select_expression_metadata;

        return [
            $select_expression_metadata,
            $applied_formatting_rules
        ];
    }


    //

    public function getReusableSelectExpressionMetadata(string $property_name): ?array
    {

        if (isset($this->select_expression_metadata_cache[$property_name])) {

            return $this->select_expression_metadata_cache[$property_name];

        } elseif ($this->containers->containsKey($property_name)) {

            $meta = $this->buildSelectExpressionMetadataForProperty(
                $this->containers->getProperty($property_name)
            );

            if (!$meta) {
                return null;
            }

            return $meta[0];

        } else {

            return null;
        }
    }


    //

    public function yieldSelectExpressionMetadataList(): \Generator
    {

        $property_collection = $this->getModel(reuse: true)->getPropertyCollection();

        foreach ($property_collection as $property) {

            $result = $this->buildSelectExpressionMetadataForProperty($property);

            if (!$result) {
                continue;
            }

            [$column_select_expression_metadata, $applied_formatting_rules] = $result;
            $class = $property->data_type_descriptor_class_name::getValueDescriptorClassName();
            $value_descriptor = new $class(
                // All values in the DB dataset, by assumption, are valid.
                ValidityEnum::VALID,
                $applied_formatting_rules
            );

            $property->onBeforeSetValue(
                function (mixed $property_value, BaseProperty $property) use ($applied_formatting_rules): ?DataTypeValueContainer {

                    if (
                        ($property_value instanceof DataTypeValueContainer)
                        && ($descriptor = $property_value->getDescriptor())
                        && $descriptor->validity === ValidityEnum::VALID
                    ) {

                        if ($applied_formatting_rules) {
                            $descriptor->addFormattingRulesIfNotMatching($applied_formatting_rules);
                        }

                        return $property_value;
                    }

                    // Nullables are valid, and if it's not a "null" value container, return back the null value.
                    return ($property_value !== null || ($property->data_type_class_name instanceof NullDataTypeValueContainer))
                        ? new ($property->data_type_class_name)($property_value, $value_descriptor)
                        : $property_value;
                },
                // Run the callback only once.
                once: true,
                priority: -1
            );

            yield $column_select_expression_metadata;
        }
    }
}
