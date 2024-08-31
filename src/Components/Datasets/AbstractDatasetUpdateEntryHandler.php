<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Common\Conditions\Condition;
use LWP\Components\Datasets\Exceptions\UpdateEntryException;
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;
use LWP\Components\DataTypes\DataTypeValueContainer;

abstract class AbstractDatasetUpdateEntryHandler extends StoreEntryHandler
{
    private ?array $model_result = null;
    public readonly string $primary_container_name;
    public readonly int|string $primary_container_value;
    protected array $changed_properties_data_array;


    public function __construct(
        public readonly array $data,
        RelationalPropertyModel $model,
        AbstractDatasetUpdateManager $manager,
        // Data that must be set through private channel
        public readonly array $private_data = [],
        ?DatasetManagementProcessInterface $process = null
    ) {

        $this->primary_container_name = $manager->store_handle->dataset->getPrimaryContainerName();

        if (!isset($model->{$this->primary_container_name})) {
            throw new \Exception(sprintf(
                "Primary container (%s) value is compulsory",
                $this->primary_container_name
            ));
        }

        parent::__construct($manager, $model, $process);

        $this->primary_container_value = $model->{$this->primary_container_name};
        // When property values are changed, make sure dependant property values are set automatically.
        $manager->store_handle->dataset->setupModelPopulateCallbacks($model);
        $manager->store_handle->setupDatasetConstraintsInModel($model);

        $this->detachRelatedProperties($data);

        // Starts tracking property value changes inside the data model.
        $model->startTrackingChanges();
        $model->setMass($data);

        // Data that must be set through private channel
        if ($private_data) {
            $model->occupySetAccessControlStack();
            $model->setMass($private_data);
            $model->deoccupySetAccessControlStack();
        }

        // Stops tracking changes.
        $changed_data = $model->stopTrackingChanges();
        unset($changed_data[$this->primary_container_name]);
        $this->changed_properties_data_array = $changed_data;
    }


    // Returns the array of properties whose values have changed

    public function getChangedProperties(): array
    {

        return $this->changed_properties_data_array;
    }


    // Returns the model

    public function getModel(): RelationalPropertyModel
    {

        $model = clone $this->model;
        $model->unsetAllValues();
        $model->occupySetAccessControlStack();
        $model->setMass($this->changed_properties_data_array);
        $model->deoccupySetAccessControlStack();

        return $model;
    }


    // Gets the value and error array representation of the model

    public function getValidationResult(bool $revalidate = false): array
    {

        if (!$this->model_result || $revalidate) {
            $this->model_result = $this->model->getValuesWithMessages(add_index: true);
        }

        return $this->model_result;
    }


    //

    public function updateModelProperty(string $property_name, mixed $value): void
    {

        $value = ($value instanceof DataTypeValueContainer)
            ? $value->getValue()
            : $value;

        $this->changed_properties_data_array[$property_name] = $value;
        parent::updateModelProperty($property_name, $value);
    }


    // Runs the main update action

    public function updateMain(): array
    {

        $collection_with_changed_properties = $this->manager->store_handle->getReusableDefinitionCollectionSet()->filterByKeys(
            array_keys($this->changed_properties_data_array)
        );
        $dataset = $this->manager->store_handle->dataset;
        $condition = new Condition('unique', 'loose');
        $loosely_unique_definition_array = $collection_with_changed_properties->matchCondition($condition)->toArray();
        $data_to_update = $this->changed_properties_data_array;

        foreach ($data_to_update as $property_name => $property_value) {
            // Filters out virtual containers (note: they will remain in update model though)
            if (!$dataset->containers->isVirtualContainer($property_name)) {
                $data_to_update[$property_name] = $this->prepareValueForStore($property_name, $property_value);
            } else {
                unset($data_to_update[$property_name]);
            }
        }

        if ($loosely_unique_definition_array) {
            // Locks unique columns for update; also, auto generates unique values, if the given ones exist
            $data_to_update = $dataset->lockAndSolveUniqueContainers($data_to_update, $loosely_unique_definition_array);
        }

        if ($this->process->auto_commitment || $this->process->commitment) {

            try {

                $updated_data = $dataset->update(
                    container_name: $this->primary_container_name,
                    field_value: $this->primary_container_value,
                    data: $data_to_update,
                    product: false,
                );

            } catch (\Throwable $exception) {

                throw new UpdateEntryException(
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception
                );
            }

        } else {

            $updated_data = $data_to_update;
        }

        $representational_name = $dataset->getRepresentationalName();
        $entry_data = [
            ...$this->model->getValues(),
            ...$updated_data,
        ];
        $primary_container_name = $dataset->getPrimaryContainerName();

        return [
            'status' => DatasetActionStatusEnum::SUCCESS->value,
            'data' => [
                $representational_name => [
                    $this->primary_container_value => [
                        'status' => DatasetActionStatusEnum::SUCCESS->value,
                        $primary_container_name => $entry_data[$primary_container_name],
                        'updated' => $updated_data,
                        // Some updates (eg. scrambled unique values) will not be in the data model.
                        'entry_data' => $entry_data,
                    ],
                ],
            ],
        ];
    }
}
