<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Common\Common;
use LWP\Common\Criteria;
use LWP\Components\Datasets\AbstractDatasetFetchManager;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Iterators\IndexableSelectIterator;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;
use LWP\Components\Datasets\Exceptions\ActionParamsError;
use LWP\Common\String\Clause\SortByComponent;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\ModelCollection;
use LWP\Filesystem\Iterators\CustomDataImportIterator;
use LWP\Components\Datasets\DatasetResult;
use LWP\Components\Datasets\AbstractDatasetSelectHandle;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;

class FilesystemDirectoryDatasetFetchManager extends AbstractDatasetFetchManager
{
    public function __construct(
        FilesystemDirectoryDataset $dataset
    ) {

        parent::__construct($dataset);
    }


    //

    public function getSingleByUniqueContainer(
        AbstractDatasetSelectHandle $select_handle,
        string $container_name,
        string|int|float $field_value,
        bool $throw_not_found = false
    ): FilesystemDirectoryDatasetDataServerContext {

        $reader = $this->dataset->directory->getReader();
        $condition = new Condition($container_name, $field_value);
        $reader->conditions($condition);

        if ($throw_not_found && $reader->count() === 0) {
            throw new EntryNotFoundException(sprintf(
                "No entry was found where field value of container \"%s\" would match \"%s\"",
                $container_name,
                $field_value
            ));
        }

        $iterator = new IndexableSelectIterator($reader, $select_handle->getSelectList());
        $iterator = new CustomDataImportIterator($iterator);

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator)
        );
    }


    //

    public function getSingleByPrimaryContainer(
        AbstractDatasetSelectHandle $select_handle,
        string|int|float $primary_container_value,
        bool $throw_not_found = false
    ): FilesystemDirectoryDatasetDataServerContext {

        $this->validateSelectHandle($select_handle);

        return $this->getSingleByUniqueContainer(
            $select_handle,
            $this->dataset->getPrimaryContainerName(),
            $primary_container_value,
            $throw_not_found
        );
    }


    //

    public function getAll(AbstractDatasetSelectHandle $select_handle): FilesystemDirectoryDatasetDataServerContext
    {

        $reader = $this->dataset->directory->getReader();
        $iterator = new IndexableSelectIterator($reader, $select_handle->getSelectList());
        $iterator = new CustomDataImportIterator($iterator);

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator)
        );
    }


    //

    public function list(
        AbstractDatasetSelectHandle $select_handle,
        ?EnhancedPropertyModel $action_params = null,
        ?EnhancedPropertyModel $filter_params = null
    ): FilesystemDirectoryDatasetDataServerContext {

        if (!$action_params) {
            $action_params = self::getModelForActionType('read');
        }

        [$original_offset, $calculated_offset] = $this->validateActionParamsBeforeResult($action_params);
        $iterator = $this->dataset->directory->getReader();
        $root_condition_group = new ConditionGroup();
        $search_query = null;

        if (isset($action_params->search_query)) {

            $search_query = $action_params->search_query;
            $root_condition_group->add($this->buildConditionGroupForSearchable($select_handle, $search_query));

            if ($action_params->search_query_mark) {
                $model = $select_handle->getModel();
                $this->markSearchMatches($model, $search_query, $select_handle);
            }
        }

        if ($filter_params) {
            $root_condition_group->add($filter_params->toConditionGroup());
        }

        if ($root_condition_group->count() !== 0) {
            $iterator->conditions($root_condition_group);
        }

        $no_limit_count = iterator_count($iterator);

        if (isset($action_params->sort)) {

            $sort_params = explode(',', $action_params->sort);
            $order_params = explode(',', $action_params->order);

            $iterator->sort(
                SortByComponent::combineIntoString($sort_params, $order_params, order_case_sensitive: false)
            );
        }

        $iterator->offset($calculated_offset);
        $iterator->limit($action_params->limit);

        [$paging_size, $max_pages] = $this->validateActionParamsAfterResult($action_params, $original_offset, $no_limit_count);
        $iterator = new IndexableSelectIterator($iterator, $select_handle->getSelectList());
        $iterator = new CustomDataImportIterator($iterator);

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator),
            $no_limit_count,
            $action_params,
            ($model ?? null)
        );
    }


    //

    public function findMatch(
        AbstractDatasetSelectHandle $select_handle,
        BasePropertyModel $model,
        bool $return_array = false,
        bool $exclude_prime = false,
        ?BasePropertyModel $model_for_default_case = null,
        bool $required_when_not_available = true,
        ?array $compare_main_unique_case_participants = null
    ): ?iterable {

        $condition_group = $this->dataset->buildStandardUniqueCase(
            $model,
            exclude_prime: $exclude_prime,
            model_for_default_case: $model_for_default_case,
            required_when_not_available: $required_when_not_available,
            compare_main_unique_case_participants: $compare_main_unique_case_participants
        );

        if (!$condition_group) {
            return null;
        }

        $reader = $this->dataset->directory->getReader();
        $reader->conditions($condition_group);

        // Since matching deals with single result only, it should be optimal enough to use `iterator_count`
        if (iterator_count($reader) === 0) {
            return null;
        }

        return (!$return_array)
            ? $reader
            : $reader->getFirst()->getIndexableData();
    }


    //

    public function findMatches(
        AbstractDatasetSelectHandle $select_handle,
        ModelCollection $model_collection,
        bool $iterate_as_array = false,
        bool $use_rcte = true
    ): ?iterable {

        $condition_group = $this->modelCollectionToUniqueCaseConditionGroup($model_collection, $use_rcte);
        $iterator = $this->dataset->directory->getReader();
        $iterator->conditions($condition_group);
        $found = false;

        foreach ($iterator as $file) {
            $found = true;
            break;
        }

        if ($iterate_as_array) {
            $iterator = new IndexableSelectIterator($iterator, $select_handle->getSelectList());
            $iterator = new CustomDataImportIterator($iterator);
        }

        return ($found)
            ? $iterator
            : null;
    }


    //

    public function getByCondition(
        AbstractDatasetSelectHandle $select_handle,
        Condition $condition
    ): FilesystemDirectoryDatasetDataServerContext {

        $reader = $this->dataset->directory->getReader();
        $reader->conditions($condition);

        $iterator = new IndexableSelectIterator($reader, $select_handle->getSelectList());
        $iterator = new CustomDataImportIterator($iterator);

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator)
        );
    }


    //

    public function getByConditionGroup(
        AbstractDatasetSelectHandle $select_handle,
        ConditionGroup $condition_group,
        bool $use_rcte = false,
        int $rcte_iterator_count = 1
    ): FilesystemDirectoryDatasetDataServerContext {

        $reader = $this->dataset->directory->getReader();
        $reader->conditions($condition_group);

        $iterator = new IndexableSelectIterator($reader, $select_handle->getSelectList());
        $iterator = new CustomDataImportIterator($iterator);

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator)
        );
    }
}
