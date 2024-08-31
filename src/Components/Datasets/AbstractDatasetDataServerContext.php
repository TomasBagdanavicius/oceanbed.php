<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Datasets\Interfaces\DataServerInterface;
use LWP\Common\Pager;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Model\ModelCollection;
use LWP\Database\Result as SqlResult;
use LWP\Components\Datasets\DatasetResult;
use LWP\Components\Datasets\Interfaces\DatasetResultInterface;
use LWP\Components\Properties\Enums\HookNamesEnum;

abstract class AbstractDatasetDataServerContext implements DataServerInterface, \IteratorAggregate
{
    public function __construct(
        public readonly AbstractDatasetFetchManager $fetch_manager,
        public readonly BasePropertyModel $model,
        public readonly DatasetResultInterface $result,
        public readonly ?int $no_limit_count = null,
        public readonly ?EnhancedPropertyModel $action_params = null,
        public readonly ?EnhancedPropertyModel $filter_params = null
    ) {

    }


    //

    public function getDatasetResult(): \Traversable
    {

        return $this->result;
    }


    //

    public function getIterator(): DatasetModelDataIterator
    {

        return new DatasetModelDataIterator(
            $this->getDatasetResult(),
            $this->model,
            $this->fetch_manager->dataset
        );
    }


    // Returns the pager interface

    public function getPager(): ?Pager
    {

        if (!$this->action_params) {
            return null;
        }

        return new Pager(
            $this->no_limit_count,
            $this->action_params->limit,
            $this->action_params->page_number
        );
    }


    //

    public function getModelCollection(): ModelCollection
    {

        $model_collection = new ModelCollection();
        $data_model_iterator = $this->getIterator();

        foreach ($data_model_iterator as $model) {
            $model_collection->set($model->name ?? $model->id, $model);
        }

        return $model_collection;
    }


    //

    public function getModel(bool $field_value_extension = false): BasePropertyModel
    {

        $dataset_result = $this->getDatasetResult();
        $dataset_result_count = $dataset_result->count();

        match (true) {
            $dataset_result_count === 0 => throw new \OutOfBoundsException(
                "At least one result is required to build a data model"
            ),
            $dataset_result_count > 1 => throw new \OutOfBoundsException(
                "Too many results for a single data model build"
            ),
            default => null,
        };

        $model = clone $this->model;
        $dataset = $this->fetch_manager->dataset;
        $can_occupy_access_control_stack = ($model instanceof EnhancedPropertyModel);

        if ($can_occupy_access_control_stack) {
            $model->occupySetAccessControlStack();
        }

        $trusted_data_callback_id = 'trusted';

        /* Check if the "trusted" callback has been set earlier (eg. in select handle). Theoretically, a model can be passed without this hook, therefore this functionality is not redundant. */
        if (!$model->hasHook(HookNamesEnum::BEFORE_SET_VALUE, $trusted_data_callback_id)) {
            $trusted_data_callback_id = $dataset->assignTrustedDataCallback($model);
        }

        $model->setMass($dataset_result->getFirst());
        $dataset->unassignTrustedDataCallback($model, $trusted_data_callback_id);
        $dataset->assignModelCallbacks($model);
        $this->fetch_manager->getRelationalModelFromFullIntrinsicDefinitions($model, $field_value_extension);

        if ($can_occupy_access_control_stack) {
            $model->deoccupySetAccessControlStack();
        }

        return $model;
    }


    // Gets single model or model collection based on the dataset result number count

    public function getModelOrModelCollection(): null|BasePropertyModel|ModelCollection
    {

        $dataset_result_count = $this->getDatasetResult()->count();

        return match (true) {
            $dataset_result_count === 1 => $this->getModel(),
            $dataset_result_count > 1 => $this->getModelCollection(),
            default => null,
        };
    }
}
