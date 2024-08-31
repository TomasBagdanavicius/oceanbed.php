<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\String\Str;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Datasets\Interfaces\DataServerInterface;
use LWP\Components\Datasets\Interfaces\DatasetManagerInterface;
use LWP\Components\Datasets\DatasetManagerTrait;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Datasets\Exceptions\ActionParamsError;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\ModelCollection;
use LWP\Components\Datasets\Interfaces\DatasetResultInterface;
use LWP\Components\Datasets\Exceptions\FetchException;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilderWithExecution;

abstract class AbstractDatasetFetchManager implements DatasetManagerInterface
{
    use DatasetManagerTrait;


    public function __construct(
        public readonly DatasetInterface $dataset
    ) {

    }


    //

    abstract public function getSingleByUniqueContainer(
        AbstractDatasetSelectHandle $select_handle,
        string $container_name,
        string|int|float $field_value,
        bool $throw_not_found = false
    ): DataServerInterface;


    //

    abstract public function getSingleByPrimaryContainer(
        AbstractDatasetSelectHandle $select_handle,
        string|int|float $primary_container_value,
        bool $throw_not_found = false
    ): DataServerInterface;


    //

    abstract public function getByCondition(
        AbstractDatasetSelectHandle $select_handle,
        Condition $condition
    ): DataServerInterface;


    //

    abstract public function getByConditionGroup(
        AbstractDatasetSelectHandle $select_handle,
        ConditionGroup $condition_group,
        bool $use_rcte = false,
        int $rcte_iterator_count = 1
    ): DataServerInterface;


    //

    abstract public function findMatch(
        AbstractDatasetSelectHandle $select_handle,
        BasePropertyModel $model,
        bool $return_array = false,
        bool $exclude_prime = false,
        ?BasePropertyModel $model_for_default_case = null,
        bool $required_when_not_available = true,
        ?array $compare_main_unique_case_participants = null
    ): ?iterable;


    //

    abstract public function findMatches(
        AbstractDatasetSelectHandle $select_handle,
        ModelCollection $model_collection,
        bool $iterate_as_array = false,
        bool $use_rcte = true
    ): ?iterable;


    //

    abstract public function getAll(AbstractDatasetSelectHandle $select_handle): DataServerInterface;


    //

    abstract public function list(
        AbstractDatasetSelectHandle $select_handle,
        ?EnhancedPropertyModel $action_params = null,
        ?EnhancedPropertyModel $filter_params = null
    ): AbstractDatasetDataServerContext;


    //

    public function filterByValues(AbstractDatasetSelectHandle $select_handle, string $container_name, array $field_values): AbstractDatasetDataServerContext
    {

        $condition_group = new ConditionGroup();

        foreach ($field_values as $value) {
            $condition_group->add(new Condition($container_name, $value), NamedOperatorsEnum::OR);
        }

        return $this->getByConditionGroup($select_handle, $condition_group);
    }


    //

    public function filterByPairs(
        AbstractDatasetSelectHandle $select_handle,
        array $data,
        NamedOperatorsEnum $operator = NamedOperatorsEnum::AND
    ): AbstractDatasetDataServerContext {

        return $this->getByConditionGroup($select_handle, ConditionGroup::fromArray($data, $operator));
    }


    //

    public function buildConditionGroupForSearchable(
        AbstractDatasetSelectHandle $select_handle,
        string $search_query,
        bool $case_sensitive = false,
        bool $accent_sensitive = false
    ): ConditionGroup {

        $searchable_containers = $select_handle->getSearchablePropertyNames();
        $condition_group = new ConditionGroup();

        if ($searchable_containers) {

            foreach ($searchable_containers as $searchable_container_name) {

                $condition = new Condition(
                    $searchable_container_name,
                    $search_query,
                    ConditionComparisonOperatorsEnum::CONTAINS,
                    case_sensitive: $case_sensitive,
                    accent_sensitive: $accent_sensitive
                );
                $condition_group->add($condition, NamedOperatorsEnum::OR);
            }
        }

        return $condition_group;
    }


    //

    public static function getActionTypeDefinitionDataArrays(): array
    {
        return [
            'read' => [
                'limit' => [
                    'type' => 'integer',
                    // Zero is for unlimited
                    'min' => 0,
                    'max' => 1000,
                    'default' => 25,
                    'description' => "Number of entries limited to a single page",
                ],
                'offset' => [
                    'type' => 'integer',
                    'min' => 0,
                    // Up to 1 billion
                    'max' => 999999999,
                    'default' => 0,
                    'description' => "List offset",
                ],
                'sort' => [
                    'type' => 'string',
                    'max' => 100,
                    'description' => "Sort container",
                ],
                'order' => [
                    'type' => 'string',
                    'in_set' => [
                        'asc',
                        'desc',
                    ],
                    'default' => 'asc',
                    'description' => "Sort order",
                ],
                'page_number' => [
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 10000,
                    'default' => 1,
                    'description' => "Page number",
                ],
                'search_query' => [
                    'type' => 'string',
                    'max' => 100,
                    'allow_empty' => false,
                    'description' => "Search query",
                ],
                'search_query_mark' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => "Defines whether mark elements should be added into searchable strings when there is a search query",
                ],
                'relationship' => [
                    'type' => 'integer',
                    'min' => 1,
                    'max' => 10000,
                    'nullable' => true,
                    'description' => "Relationship ID",
                ],
                'node_key' => [
                    'type' => 'string',
                    'min' => 1,
                    'max' => 25,
                    'nullable' => true,
                    'description' => "Node key",
                ],
                'count' => [
                    'type' => 'integer',
                    'min' => 0,
                    'max' => 1000,
                    'set_access' => 'private',
                    'description' => "The number of entries in current page",
                ],
            ],
        ];
    }


    //

    public function validateActionParamsBeforeResult(
        EnhancedPropertyModel $action_params
    ): array {

        $action_param_values = $action_params->getValuesWithMessages(add_index: true);

        if ($action_param_values['__index']['error_count']) {
            throw new \Exception("Action params contain errors");
        }

        // Offset by page number.
        $original_offset = $action_params->offset;
        $calculated_offset = ($original_offset + (($action_params->page_number - 1) * $action_params->limit));

        try {

            /* Alternatively, a separate property could be used for the calculated offset in case both values are needed. Currently there is just one. */
            $action_params->offset = $calculated_offset;

        } catch (PropertyValueContainsErrorsException $exception) {

            throw new ActionParamsError(
                "Action parameter \"offset\" is invalid",
                previous: $exception
            );
        }

        return [
            $original_offset,
            $calculated_offset
        ];
    }


    //

    public function validateActionParamsAfterResult(
        EnhancedPropertyModel $action_params,
        int $original_offset,
        int $no_limit_count,
    ): array {

        if ($no_limit_count !== 0 && $original_offset >= $no_limit_count) {
            throw new ActionParamsError(sprintf(
                "Value of parameter \"offset\" is invalid: it cannot exceed %d",
                ($no_limit_count - 1)
            ));
        }

        // When zero it tranlates into all/unlimited
        $limit = ($action_params->limit ?: $no_limit_count);
        $page_number = $action_params->page_number;
        /* The number of entries that can actually be paged. When offset is provided by user, it shrinks the overall range, which is normally the "no_limit_count". */
        $paging_size = ($no_limit_count - $original_offset);
        $max_pages = (int)ceil($paging_size / $limit);

        if ($max_pages && $page_number > $max_pages) {
            throw new ActionParamsError(sprintf(
                "Value of parameter \"page_number\" is invalid: it cannot exceed %d",
                $max_pages
            ));
        }

        $action_params->occupySetAccessControlStack();
        $action_params->count = match (true) {
            !$max_pages => 0,
            $page_number !== $max_pages => $limit,
            default => (($no_limit_count - ($limit * ($page_number - 1))) - $original_offset)
        };
        $action_params->deoccupySetAccessControlStack();

        return [
            $paging_size,
            $max_pages
        ];
    }


    //

    public function markSearchMatches(BasePropertyModel $model, string $search_query, AbstractDatasetSelectHandle $select_handle): void
    {

        $search_query_is_ascii = Str::isAscii($search_query);
        $search_query_len = ($search_query_is_ascii)
            ? strlen($search_query)
            : mb_strlen($search_query);

        $model->onAfterGetValue(function (
            mixed $property_value,
            BasePropertyModel $model,
            string $property_name
        ) use (
            $search_query,
            $search_query_len,
            $search_query_is_ascii,
            $select_handle,
        ): mixed {

            $schema = $select_handle->containers->getDefinitionsForContainer($property_name);

            if (!empty($schema['searchable'])) {

                $property_value = (string)$property_value;
                $property_value_is_ascii = Str::isAscii($property_value);
                $positions = ($property_value_is_ascii && $search_query_is_ascii)
                    ? Str::posAll($property_value, $search_query, case_sensitive: false)
                    : Str::accentInsensitivePosAll($property_value, $search_query, case_sensitive: false);

                if ($positions) {

                    $open_tag = '<mark class="sq">';
                    $close_tag = '</mark>';

                    if ($property_value_is_ascii) {
                        $property_value = Str::stringWrap($property_value, $positions, $search_query_len, $open_tag, $close_tag);
                    } else {
                        // Prevents diacritic information loss
                        $property_value = \Normalizer::normalize($property_value, \Normalizer::FORM_C);
                        $property_value = Str::mbStringWrap($property_value, $positions, $search_query_len, $open_tag, $close_tag);
                    }
                }
            }

            return $property_value;

        });
    }


    //

    public function modelCollectionToUniqueCaseConditionGroup(ModelCollection $model_collection, bool $use_rcte = false): ?ConditionGroup
    {

        if (!$model_collection->count()) {
            return null;
        }

        $root_condition_group = new ConditionGroup();
        $i = 0;

        foreach ($model_collection as $model) {

            $args = [
                $model
            ];

            if ($use_rcte) {
                $args['rcte_id'] = $i;
            }

            $condition_group = $this->dataset->buildStandardUniqueCase(...$args);

            if ($condition_group) {
                $root_condition_group->add($condition_group, NamedOperatorsEnum::OR);
            }

            $i++;
        }

        return (!$root_condition_group->count())
            ? null
            : $root_condition_group;
    }


    //

    public function initDataServerContext(
        AbstractDatasetSelectHandle $select_handle,
        DatasetResultInterface $result,
        ?int $no_limit_count = null,
        ?EnhancedPropertyModel $action_params = null,
        ?BasePropertyModel $model = null,
        ?EnhancedPropertyModel $filter_params = null
    ): DataServerInterface {

        $class_name = $select_handle->getDataServerContextClassName();

        return new ($class_name)(
            $this,
            ($model ?: $select_handle->getModel()),
            $result,
            $no_limit_count,
            $action_params,
            $filter_params
        );
    }


    //

    public function getRelationalModelFromFullIntrinsicDefinitions(
        BasePropertyModel $model,
        bool $field_value_extension = true
    ): void {

        if ($field_value_extension) {
            $this->dataset->enableFieldValueExtension($model);
        }
    }


    // Checks whether given select handle has originated from dataset associated with this manager

    public function validateSelectHandle(AbstractDatasetSelectHandle $select_handle): void
    {

        if ($select_handle->dataset !== $this->dataset) {
            throw new \Exception(sprintf(
                "Select handle %s is incompatible with %s",
                $select_handle::class,
                $this->dataset::class
            ));
        }
    }
}
