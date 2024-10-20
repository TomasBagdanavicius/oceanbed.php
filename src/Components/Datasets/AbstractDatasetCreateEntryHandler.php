<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Common;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Common\Conditions\Condition;
use LWP\Components\Datasets\Exceptions\CreateEntryException;
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;

abstract class AbstractDatasetCreateEntryHandler extends StoreEntryHandler
{
    private ?array $model_result = null;


    public function __construct(
        protected array $data,
        AbstractDatasetCreateManager $manager,
        ?DatasetManagementProcessInterface $process = null
    ) {

        $model = clone $manager->buildRelationalPropertyModel();
        parent::__construct($manager, $model, $process);

        $this->detachRelatedProperties($data);

        // Run less processes when data is empty, which is absolutely fine
        if ($data) {

            $this->model->startTrackingChanges();
            $this->model->occupySetAccessControlStack();
            $this->model->setMass($data);
            $this->model->deoccupySetAccessControlStack();
            $changed_data = $this->model->stopTrackingChanges();

            /* New values for related properties or event new related properties themselves might have been added inside `setMass`, eg. through `setupModelPopulateCallbacks` or similar. */
            if ($changed_data) {
                // Get values for target elements only
                $values = $model->getValues($this->manager->store_handle->related_property_list);
                $this->detachRelatedProperties($values, merge: true);
            }
        }

        $this->manager->store_handle->dataset->modelFillIn($this->model);
    }


    // Gets the model that is representing the entry

    public function getModel(): RelationalPropertyModel
    {

        return $this->model;
    }


    // Gets the value and error array representation of the model.

    public function getValidationResult(bool $revalidate = false): array
    {

        if (!$this->model_result || $revalidate) {
            $this->model_result = $this->model->getValuesWithMessages(add_index: true);
        }

        return $this->model_result;
    }


    // Gets data that should be inserted.

    public function getData(bool $revalidate = false): array
    {

        $data_to_create = [];
        $store_handle = $this->manager->store_handle;
        $validation_result = $this->getValidationResult($revalidate);

        foreach ($validation_result as $identifier => $data) {

            if (
                // Special identifier
                $identifier === '__index'
                // Not a foreign container
                || $store_handle->dataset->foreignContainerExists($identifier)
                // Not a virtual container
                || $store_handle->containers->isVirtualContainer($identifier)
            ) {
                continue;
            }

            if (isset($data['value'])) {
                $data_to_create[$identifier] = $this->prepareValueForStore($identifier, $data['value']);
            }
        }

        return $data_to_create;
    }


    //

    public function createMain(): int|string
    {

        $store_handle = $this->manager->store_handle;
        $dataset = $store_handle->dataset;

        $condition = new Condition('unique', 'loose');
        // This is a definition array, not a list of containers
        $loosely_unique_definition_array = $store_handle->getReusableDefinitionCollectionSet()->matchCondition($condition)->toArray();

        if ($loosely_unique_definition_array) {

            // Locks unique columns for update; also, auto generates unique values, if the given ones exist.
            $data_to_create = $dataset->lockAndSolveUniqueContainers($this->model, $loosely_unique_definition_array);
        }

        // Revalidate, because unique value solutions might change model property values, eg. when "title" property is unique, it might affect its "name" alias
        $data_to_create = $this->getData(revalidate: true);
        $primary_container_name = $dataset->getPrimaryContainerName();

        try {

            // Either has internal "commit" handling (eg. SQL database), or "commit" is enabled
            if ($this->process->auto_commitment || $this->process->commitment) {
                $created_data = $dataset->createEntry($data_to_create, product: false);
            } else {
                $created_data = [
                    $data_to_create[$primary_container_name] => $data_to_create
                ];
            }

        } catch (\Throwable $exception) {

            throw new CreateEntryException(
                $exception->getMessage(),
                previous: $exception
            );
        }

        $create_key_value = array_key_first($created_data);
        $representational_name = $dataset->getRepresentationalName();

        if (is_numeric($create_key_value)) {
            $create_key_value = intval($create_key_value);
        }

        /* Add to result payload */

        $result = [
            'status' => DatasetActionStatusEnum::SUCCESS->value,
            'data' => [
                $representational_name => [
                    [
                        'status' => DatasetActionStatusEnum::SUCCESS->value,
                        $primary_container_name => $create_key_value,
                        'data' => $created_data[$create_key_value],
                    ],
                ],
            ],
        ];

        $this->addToResultPayload($result);
        $this->lateResult($create_key_value);

        return $create_key_value;
    }


    //

    public function lateResult(int|string $create_key_value): void
    {

        $store_handle = $this->manager->store_handle;
        $dataset = $store_handle->dataset;
        $related_store_properties = $dataset->getRelatedStoreContainerData();
        $relationship_nodes_create_data = [];

        foreach ($related_store_properties as $property_name => $build_options) {

            if (isset($this->model->{$property_name})) {

                $container = $store_handle->containers->get($property_name);
                $relationship = $container->getRelationship();
                $perspective = $container->getPerspective();
                $main_position = $perspective->position;
                $the_other_position = $perspective->getTheOtherPosition();
                $main_container_name = $relationship->node_dataset->getKeyContainerNameByPosition($main_position);
                $the_other_container_name = $relationship->node_dataset->getKeyContainerNameByPosition($the_other_position);

                $data = [
                    'title' => ('Node ' . date('Y-m-d H:i:s')),
                    'relationship' => $relationship->id,
                    $main_container_name => $create_key_value,
                    $the_other_container_name => $this->model->{$property_name},
                ];
                $relationship_nodes_create_data[] = $data;
            }
        }

        if ($relationship_nodes_create_data) {

            $relationship_nodes_dataset = $relationship->node_dataset;
            $relationship_nodes_store_handle = $relationship_nodes_dataset->getStoreHandle();
            $relationship_nodes_create_manager = $relationship_nodes_store_handle->getCreateManager([
                'process' => $this->process
            ]);

            if (count($relationship_nodes_create_data) === 1) {

                $first_key = array_key_first($relationship_nodes_create_data);
                $create_result = $relationship_nodes_create_manager->singleFromArray($relationship_nodes_create_data[$first_key], $this->process->commitment);

            } else {

                $create_result = $relationship_nodes_create_manager->manyFromArray($relationship_nodes_create_data, $this->process->commitment);
            }

            if ($create_result['status'] === 0) {
                throw new CreateEntryException("Late result validation errors");
            }

            $this->addToResultPayload($create_result);
        }
    }
}
