<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Constraints\InDatasetConstraint;
use LWP\Components\Constraints\NotInDatasetConstraint;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Enums\ReadWriteModeEnum;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueDescriptor;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\Datasets\Interfaces\DatasetStoreFieldValueFormatterInterface;
use LWP\Components\Definitions\Interfaces\WithDefinitionArrayInterface;
use LWP\Components\Definitions\DefinitionCollectionSet;

abstract class AbstractDatasetStoreHandle implements WithDefinitionArrayInterface
{
    public readonly SpecialContainerCollection $containers;
    // Related properties data array.
    public readonly ?array $related_properties_data;
    // Array list of related properties.
    public readonly ?array $related_property_list;


    public function __construct(
        public readonly DatasetInterface $dataset,
        protected array $identifiers,
        protected array $modifiers = [],
    ) {

        $this->containers = clone $dataset->containers;
        $store_containers = $dataset->getRelatedStoreContainerList();

        foreach ($store_containers as $container_name) {
            $container = $dataset->foreign_container_collection->get($container_name);
            $this->containers->add($container);
        }

        $modifiers[] = [
            'modifier' => $this->getDatasetDescriptorStoreModifier()
        ];

        $this->indexSchemaForExtrinsicContainers();

        foreach ($modifiers as $modifier_data) {
            $this->containers->setModifier($modifier_data);
        }

        $this->related_properties_data = $dataset->getRelatedReadContainerData();
        $this->related_property_list = ($this->related_properties_data)
            ? array_keys($this->related_properties_data)
            : null;
    }


    //

    abstract public function getCreateManagerClassName(): string;


    //

    abstract public function getUpdateManagerClassName(): string;


    //

    abstract public function getDeleteManagerClassName(): string;


    //

    abstract public function getDatasetStoreManagementProcessClassName(): string;


    //

    abstract public function getStoreFieldValueFormatterClassName(): string;


    //

    public function getDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return $this->containers->getDefinitionCollectionSet();
    }


    //

    public function getReusableDefinitionCollectionSet(): DefinitionCollectionSet
    {

        return $this->containers->getReusableDefinitionCollectionSet();
    }


    //

    public function getExtrinsicContainerList(): array
    {

        return $this->containers->matchBySingleCondition('container_type', 'extrinsic')->getKeys();
    }


    //

    public function indexSchemaForExtrinsicContainers(): void
    {

        $extrinsic_containers = $this->getExtrinsicContainerList();

        if ($extrinsic_containers) {

            foreach ($extrinsic_containers as $extrinsic_container) {
                $this->containers->get($extrinsic_container)->submitSchemaToIndex();
            }
        }
    }


    //

    public function getModel(): BasePropertyModel
    {

        return $this->containers->getModel();
    }


    //

    public function getCreateManager(array $extra_params = []): AbstractDatasetCreateManager
    {

        $class_name = $this->getCreateManagerClassName();

        return new $class_name($this, ...$extra_params);
    }


    //

    public function getUpdateManager(array $extra_params = []): AbstractDatasetUpdateManager
    {

        $class_name = $this->getUpdateManagerClassName();

        return new $class_name($this, ...$extra_params);
    }


    //

    public function getDeleteManager(array $extra_params = []): AbstractDatasetDeleteManager
    {

        $class_name = $this->getDeleteManagerClassName();

        return new $class_name($this, ...$extra_params);
    }


    //

    public function getDatasetDescriptorStoreModifier(): \Closure
    {

        $index = [];
        $dataset = $this->dataset;

        return static function (array $definitions) use (&$index, $dataset): array {

            if (isset($definitions['type'])) {

                $type = $definitions['type'];

                if (!array_key_exists($type, $index)) {

                    $descriptor = $dataset->getDescriptor();
                    $formatting_rule = $descriptor->getSetterFormattingRuleForDataType($type);

                    if ($formatting_rule) {
                        // "custom_options" is the user provided option array.
                        $custom_options = $index[$type] = $formatting_rule->custom_options;
                    } else {
                        $index[$type] = false;
                    }

                } elseif ($index[$type] !== false) {

                    $custom_options = $index[$type];
                }

                if (isset($custom_options)) {

                    #todo: detect if definition for this formatting rule is nested
                    if ($type === 'number') {

                        if (!empty($definitions['number_format'])) {
                            $custom_options = [...$custom_options, ...$definitions['number_format']];
                        }

                        $custom_options = ['number_format' => $custom_options];
                    }

                    $definitions = [...$definitions, ...$custom_options];
                }
            }

            return $definitions;
        };
    }


    //

    public function setupDatasetConstraintsInModel(BasePropertyModel $model)
    {

        $relationship_property_names = $this->containers->getRelationshipContainers(dataset_association_type: ReadWriteModeEnum::WRITE);

        if ($relationship_property_names) {

            foreach ($relationship_property_names as $relationship_property_name) {

                $relationship_property = $model->getPropertyByName($relationship_property_name);
                $container = $this->containers->get($relationship_property_name);
                $relationship = $container->getRelationship();
                $the_other_perspective = $container->getTheOtherPerspective();
                $the_other_dataset = $container->getTheOtherDataset();
                $the_other_position = $the_other_perspective->position;

                // Related field - not node storage
                if (!$relationship->isNode()) {

                    /* "one" type containers are naturally unique. This guards the container. */
                    if ($the_other_perspective->type_code === 1) {

                        $not_in_dataset_constraint = new NotInDatasetConstraint(
                            $this->dataset,
                            $relationship_property_name
                        );

                        $relationship_property->setConstraint($not_in_dataset_constraint);
                    }

                    // Node storage
                } else {

                    /* "one" type containers are naturally unique. This guards the container. */
                    if ($the_other_perspective->type_code === 1) {

                        $node_dataset = $relationship->node_dataset;
                        $condition_data = [
                            'parameterize' => true
                        ];

                        $relationship_condition = new Condition(
                            $node_dataset->getRelationshipFieldName(),
                            $relationship->id,
                            data: $condition_data
                        );
                        $condition_group = ConditionGroup::fromCondition($relationship_condition);
                        $length_condition = new Condition(
                            $node_dataset->getRelationshipLengthFieldName(),
                            $relationship->length,
                            data: $condition_data
                        );
                        $condition_group->add($length_condition);

                        $not_in_dataset_constraint = new NotInDatasetConstraint(
                            $node_dataset,
                            $node_dataset->getKeyContainerNameByPosition($the_other_position),
                            $condition_group
                        );

                        $relationship_property->setConstraint($not_in_dataset_constraint);
                    }
                }

                $in_dataset_constraint = new InDatasetConstraint(
                    $the_other_dataset,
                    $the_other_dataset->getPrimaryContainerName()
                );

                $relationship_property->setConstraint($in_dataset_constraint);
            }

        }
    }


    //

    public function getRelationalModelFromFullIntrinsicDefinitions(
        RelationalPropertyModel $model,
        bool $auto_population = true,
        bool $auto_fill_in = true,
        bool $dataset_exists_constraint = true,
        // Default is false, because batch unique constraint method is the preferred one.
        bool $dataset_unique_constraint = false,
        bool $field_value_extension = true
    ): void {

        $this->dataset->getRelationalModelFromFullIntrinsicDefinitions(
            $model,
            $auto_population,
            $auto_fill_in,
            $dataset_unique_constraint,
            $field_value_extension,
            containers: $this->containers
        );

        if ($dataset_exists_constraint) {
            $this->setupDatasetConstraintsInModel($model);
        }
    }


    //

    public function getDefinitionDataArray(): array
    {

        return $this->containers->getDefinitionDataArray();
    }
}
