<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Common;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueDescriptor;
use LWP\Components\Datasets\Interfaces\DatabaseStoreFieldValueFormatterInterface;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Components\Datasets\Interfaces\DatasetManagerInterface;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\detachElements;

abstract class StoreEntryHandler
{
    protected array $full_result = [];
    protected array $data_array_related = [];
    private DatabaseStoreFieldValueFormatterInterface $data_store_formatter;


    public function __construct(
        public readonly DatasetManagerInterface $manager,
        protected RelationalPropertyModel $model,
        public readonly ?DatasetManagementProcessInterface $process = null
    ) {

        $this->data_store_formatter = $manager->store_handle->dataset->database->getStoreFieldValueFormatter();
    }


    //

    public function detachRelatedProperties(array &$data, bool $merge = false): void
    {

        $related_property_list = $this->manager->store_handle->related_property_list;
        $data_array_related = ($related_property_list)
            ? detachElements($data, $related_property_list)
            : [];

        if (!$merge) {
            $this->data_array_related = $data_array_related;
        } else {
            $this->data_array_related = [...$this->data_array_related, ...$data_array_related];
        }
    }


    // Gets the result payload data array

    public function getResultPayload(): array
    {

        return $this->full_result;
    }



    // Merges given data into the result payload

    public function addToResultPayload(array $result_data): void
    {

        Common::addToUniversalPayload($this->full_result, $result_data);
    }


    //

    public function addRelatedData(): ?array
    {

        $store_handle = $this->manager->store_handle;

        if (method_exists($store_handle, 'prepareData')) {
            $this->model = $store_handle->prepareData($this->model, $this->data_array_related);
        }

        $dataset = $store_handle->dataset;
        $representational_name = $dataset->getRepresentationalName();

        if ($this->process) {

            $savepoint_name = sprintf(
                'sp_%s_%s',
                $representational_name,
                mt_rand()
            );

            $this->process->addSavepoint($savepoint_name);
        }

        // Related properties provided in the data to be created.
        if ($this->data_array_related) {

            $relationship_properties = $store_handle->containers->getRelationshipContainers();

            if ($relationship_properties) {

                $this->model->occupySetAccessControlStack();

                foreach ($relationship_properties as $property_name) {

                    $property_value_state = $this->model->getValueByPropertyName($property_name, include_messages: true);

                    if (!empty($property_value_state['errors'])) {

                        $relationship_name = $store_handle->getReusableDefinitionCollectionSet()->get($property_name)->get('relationship')->getRelationshipName();

                        #review: it would be nice to optimize the inners of this process
                        $related_dataset = $dataset->database->getRelationship($relationship_name)->getPerspectiveByDataset($dataset)->getTheOtherDataset();
                        $required_containers = $related_dataset->getRequiredContainers();
                        // Data to be inserted to this related module.
                        $create_data_array = $added_references = [];

                        // Related properties metadata.
                        foreach ($store_handle->related_properties_data as $related_property_name => $related_property_metadata) {

                            if (
                                // Related property value provided.
                                isset($this->data_array_related[$related_property_name])
                                // Matching relationship name.
                                && isset($related_property_metadata['relationship']) && $related_property_metadata['relationship'] === $relationship_name
                            ) {

                                $extrinsic_container = $dataset->addExtrinsicContainer($related_property_name, $related_property_metadata);

                                $this->model->addProperty(
                                    RelationalPropertyModel::createPropertyFromDefinitionCollection(
                                        $related_property_name,
                                        $extrinsic_container->getDefinitionCollection(),
                                        $this->model
                                    )
                                );

                                $value = $this->data_array_related[$related_property_name];
                                $this->model->{$related_property_name} = $added_references[$related_property_name] = $value;

                                $create_data_array[$related_property_metadata['property_name']] = $value;
                                $required_containers_key = array_search($related_property_metadata['property_name'], $required_containers);

                                // Is one of the required containers
                                if ($required_containers_key !== false) {
                                    unset($required_containers[$required_containers_key]);
                                }
                            }
                        }

                        /* Checking if all required containers have been cleared is a simplified method to verify that there is enough data to create a related entry. There can be other factors, such as aliases, auto populated values, etc. If not all required containers have been cleared, attempt model plotting. It could be an idea to rebuild entirely based on model plotting and remove required checking, but the current approach is optimal enough. */
                        if ($required_containers) {

                            $related_dataset_store_handle = $related_dataset->getStoreHandle();
                            $related_dataset_model = $related_dataset_store_handle->getModel();
                            $related_dataset_store_handle->getRelationalModelFromFullIntrinsicDefinitions(
                                $related_dataset_model,
                                /* Choose only essential options. */
                                auto_population: true,
                                auto_fill_in: true,
                                dataset_unique_constraint: false,
                                field_value_extension: false
                            );
                            $related_dataset_model->setMass($create_data_array);
                            $values = $related_dataset_model->getValuesWithMessages(add_index: true);

                            if ($values['__index']['error_count'] === 0) {
                                // No errors, good to clear all
                                $required_containers = [];
                            }
                        }

                        // All required containers have been cleared.
                        if (!$required_containers) {

                            try {

                                $related_create_manager = $related_dataset->getStoreHandle()->getCreateManager([
                                    'process' => $this->process,
                                ]);

                                $related_create_result = $related_create_manager->singleFromArray($create_data_array);

                                // Related data inserted successfully.
                                if (!empty($related_create_result['status'])) {

                                    $related_representational_name = $related_dataset->getRepresentationalName();
                                    $dataset_result = $related_create_result['data'][$related_representational_name];
                                    $primary_container_name = $dataset->getPrimaryContainerName();

                                    $value = $added_references[$property_name] = $dataset_result[array_key_last($dataset_result)][$primary_container_name];
                                    $value = new IntegerDataTypeValueContainer($value, new IntegerDataTypeValueDescriptor(ValidityEnum::VALID));

                                    // Add reference value.
                                    $this->updateModelProperty($property_name, $value);
                                    $this->addToResultPayload($related_create_result);

                                    // Invalid status
                                } else {

                                    $this->process->rollbackSavepoint();
                                }

                            } catch (\Throwable $exception) {

                                $this->process->rollbackSavepoint();

                                throw $exception;
                            }
                        }
                    }
                }

                $this->model->deoccupySetAccessControlStack();
            }
        }

        if ($this->process) {

            $validation_result = $this->getValidationResult(revalidate: true);

            if (!empty($validation_result['__index']['error_count'])) {
                // Will rollback changes made to related datasets.
                $this->process->rollbackSavepoint();
            } else {
                $this->process->releaseSavepoint();
            }
        }

        return ($added_references ?? null);
    }


    //

    public function updateModelProperty(string $property_name, mixed $value): void
    {

        $this->model->{$property_name} = $value;
    }


    //

    public function prepareValueForStore(string $container_name, mixed $value): mixed
    {

        $store_format = true;
        $store_handle = $this->manager->store_handle;
        $dataset = $store_handle->dataset;
        $data_type = $store_handle->containers->getDataTypeForContainer($container_name);
        $store_formatting_rule = $this->data_store_formatter->willUseFormattingRule($value, $data_type);

        /* When property is using a formatting rule, store's formatting rule should not be applied. Example: table stores a number as varchar and corresponding property contains "NumberFormattingRule". The latter should take precedence over the default store's formatting rule. */
        if ($store_formatting_rule) {

            $property = $this->model->getPropertyByName($container_name);
            $store_format = !$property->hasFormattingRule($store_formatting_rule::class);
        }

        return ($store_format)
            ? $this->data_store_formatter->formatByDataType($value, $data_type)
            : $value;
    }
}
