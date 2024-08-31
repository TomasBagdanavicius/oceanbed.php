<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Common\Iterators\ArrayColumnSelectIterator;
use LWP\Components\Datasets\AbstractDatasetFetchManager;
use LWP\Components\Datasets\Interfaces\DataServerInterface;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Common\Criteria;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\String\Clause\SortByComponent;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\ModelCollection;
use LWP\Components\Datasets\DatasetResult;
use LWP\Components\Datasets\AbstractDatasetSelectHandle;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;

class ArrayCollectionDatasetFetchManager extends AbstractDatasetFetchManager
{
    public function __construct(
        ArrayCollectionDataset $dataset
    ) {

        parent::__construct($dataset);
    }


    //

    public function getSingleByUniqueContainer(
        AbstractDatasetSelectHandle $select_handle,
        string $container_name,
        int|string|float $field_value,
        bool $throw_not_found = false
    ): ArrayCollectionDatasetDataServerContext {

        $collection = $this->dataset->column_array_collection;
        $collection = $collection->matchSingleEqualToCondition($container_name, $field_value);

        if ($throw_not_found && $collection->count() === 0) {
            throw new EntryNotFoundException(sprintf(
                "No entry was found where field value of container \"%s\" would match \"%s\"",
                $container_name,
                $field_value
            ));
        }

        $iterator = $collection->getIterator();
        $iterator = new ArrayCollectionDatasetRelationshipIterator($iterator, $select_handle);
        $iterator = new ArrayColumnSelectIterator($iterator, $select_handle->getSelectList());

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator)
        );
    }


    //

    public function getSingleByPrimaryContainer(
        AbstractDatasetSelectHandle $select_handle,
        int|string|float $primary_container_value,
        bool $throw_not_found = false
    ): ArrayCollectionDatasetDataServerContext {

        return $this->getSingleByUniqueContainer(
            $select_handle,
            $this->dataset->getPrimaryContainerName(),
            $primary_container_value
        );
    }


    //

    public function getAll(AbstractDatasetSelectHandle $select_handle): ArrayCollectionDatasetDataServerContext
    {

        $iterator = $this->dataset->column_array_collection->getIterator();
        $iterator = new ArrayColumnSelectIterator($iterator, $select_handle->getSelectList());

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
    ): ArrayCollectionDatasetDataServerContext {

        if (!$action_params) {
            $action_params = self::getModelForActionType('list');
        }

        $property_list = $select_handle->getSelectList();
        [$original_offset, $calculated_offset] = $this->validateActionParamsBeforeResult($action_params);
        $collection = $this->dataset->column_array_collection;

        $root_condition_group = new ConditionGroup();

        if (isset($action_params->search_query)) {

            $search_query = $action_params->search_query;
            $root_condition_group->add($this->buildConditionGroupForSearchable($select_handle, $action_params->search_query));

            if ($action_params->search_query_mark) {
                $model = $select_handle->getModel();
                $this->markSearchMatches($model, $search_query, $select_handle);
            }
        }

        if ($filter_params) {
            $root_condition_group->add($filter_params->toConditionGroup());
        }

        if ($root_condition_group->count() !== 0) {
            $collection = $collection->matchConditionGroup($root_condition_group);
        }

        $iterator = $collection->getIterator();
        $no_limit_count = iterator_count($iterator);

        $criteria = new Criteria();
        $criteria->limit($action_params->limit);
        $criteria->offset($action_params->offset);

        if (isset($action_params->sort)) {

            $sort_params = explode(',', $action_params->sort);
            $order_params = explode(',', $action_params->order);
            $sort_by_str = SortByComponent::combineIntoString($sort_params, $order_params, order_case_sensitive: false);
            $criteria->sort($sort_by_str);
        }

        $collection = $collection->matchCriteria($criteria);
        $iterator = $collection->getIterator();

        [$paging_size, $max_pages] = $this->validateActionParamsAfterResult($action_params, $original_offset, $no_limit_count);

        $iterator = new ArrayCollectionDatasetRelationshipIterator($iterator, $select_handle);
        $iterator = new ArrayColumnSelectIterator($iterator, $property_list);

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

        $collection = $this->dataset->column_array_collection;
        $filtered_collection = $collection->matchConditionGroup($condition_group);

        if ($filtered_collection->count() === 0) {
            return null;
        }

        return (!$return_array)
            ? $filtered_collection->getIterator()
            : $filtered_collection->getFirst();
    }


    //

    public function findMatches(
        AbstractDatasetSelectHandle $select_handle,
        ModelCollection $model_collection,
        bool $iterate_as_array = false,
        bool $use_rcte = true
    ): ?iterable {

        $condition_group = $this->modelCollectionToUniqueCaseConditionGroup($model_collection, $use_rcte);
        $collection = $this->dataset->column_array_collection;

        if ($use_rcte) {

            $index_tree = $collection->getIndexTree();
            $cached_indexes = [];

            $indexes = $index_tree->assessConditionGroup(
                $condition_group,
                function (
                    Condition $condition,
                    ?array $assessment_indexes
                ) use (
                    &$cached_indexes,
                    $collection,
                ): ?array {

                    // The special RCTE index key
                    if ($condition->keyword === 'rcte_i') {

                        $to_return = $cached_indexes;

                        if ($to_return) {
                            foreach ($to_return as $index) {
                                $collection->updateValue($index, 'rcte_id', $condition->value);
                            }
                        }

                        $cached_indexes = [];

                        return $to_return;

                    } elseif ($assessment_indexes) {

                        $cached_indexes = [...$cached_indexes, ...$assessment_indexes];
                    }

                    return $assessment_indexes;
                }
            );

            // Matching indexes found.
            if ($indexes) {

                $data_set = array_intersect_key($collection->toArray(), array_flip($indexes));
                $filtered_collection = $collection->fromArgs($data_set, [
                    'element_list' => [...$collection->getElementList(), ...['rcte_id']],
                ]);

            } else {

                return null;
            }

            // No RCTE
        } else {

            $filtered_collection = $collection->matchConditionGroup($condition_group);
        }

        return ($filtered_collection->count() !== 0)
            ? $filtered_collection->getIterator()
            : null;
    }


    //

    public function getByCondition(
        AbstractDatasetSelectHandle $select_handle,
        Condition $condition
    ): ArrayCollectionDatasetDataServerContext {

        $collection = $this->dataset->column_array_collection;
        $filtered_collection = $collection->matchCondition($condition);

        $iterator = $filtered_collection->getIterator();
        $iterator = new ArrayColumnSelectIterator($iterator, $select_handle->getSelectList());

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
    ): ArrayCollectionDatasetDataServerContext {

        $collection = $this->dataset->column_array_collection;
        $filtered_collection = $collection->matchConditionGroup($condition_group);

        $iterator = $filtered_collection->getIterator();
        $iterator = new ArrayColumnSelectIterator($iterator, $select_handle->getSelectList());

        return $this->initDataServerContext(
            $select_handle,
            new DatasetResult($iterator)
        );
    }
}
