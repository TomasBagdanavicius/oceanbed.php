<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\Common;
use LWP\Common\String\Str;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\String\Clause\SortByComponent;
use LWP\Components\Datasets\AbstractDatasetFetchManager;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Model\ModelCollection;
use LWP\Database\SyntaxBuilders\FetchQueryBuilder;
use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilder;
use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilderWithExecution;
use LWP\Database\Server as SqlServer;
use LWP\Components\Datasets\Relationships\RelationshipNodeKey;
use LWP\Components\Datasets\AbstractDatasetSelectHandle;
use LWP\Components\Datasets\Exceptions\FetchException;
use LWP\Components\Datasets\Exceptions\EntryNotFoundException;
use LWP\Components\Attributes\NoValueAttribute;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

class TableDatasetFetchManager extends AbstractDatasetFetchManager
{
    public function __construct(
        Table $dataset
    ) {

        parent::__construct($dataset);
    }


    // Returns parts for the main query

    public function getMainSqlQueryParts(): array
    {

        // The default value
        return [];
    }


    // Injects sql query parts into a given SQL query builder

    public function injectMainSqlQueryParts(BasicSqlQueryBuilderWithExecution $basic_sql_query_builder): void
    {

        $parts = $this->getMainSqlQueryParts();

        if ($parts) {

            if (isset($parts['join'])) {

                foreach ($parts['join'] as $join) {
                    $basic_sql_query_builder->join($join['string'], params: $join['params'] ?? []);
                }
            }

            if (isset($parts['where'])) {

                foreach ($parts['where'] as $where) {
                    ['string' => $string, 'operator' => $operator] = $where;
                    $basic_sql_query_builder->where($string, $operator, params: $where['params'] ?? []);
                }
            }
        }
    }


    //

    public function getFundamentalBasicSqlBuilder(AbstractDatasetSelectHandle $select_handle): BasicSqlQueryBuilderWithExecution
    {

        $abbreviation = $this->dataset->getAbbreviation();
        // Not using "all" symbol syntax, because specific columns might need formatting
        $basic_sql_query_builder = (new BasicSqlQueryBuilderWithExecution($this->dataset->server))->selectFromMetadata($select_handle->yieldSelectExpressionMetadataList());
        $basic_sql_query_builder->from($this->dataset->name, $abbreviation);
        $this->injectMainSqlQueryParts($basic_sql_query_builder);

        return $basic_sql_query_builder;
    }


    //

    private function transferOptions(array $join_options): array
    {

        /* Transfers `join_options`. Primarily used to join default relationship node and transfer `join_options` from config. */
        if ($this->dataset->foreign_container_collection->count() !== 0) {
            foreach ($this->dataset->foreign_container_collection as $container) {
                if ($container->join_options) {
                    if (!isset($join_options[$container->relationship_name])) {
                        $join_options[$container->relationship_name] = $container->join_options;
                    } else {
                        $join_options[$container->relationship_name] = [...$join_options[$container->relationship_name], ...$container->join_options];
                    }
                }
            }
        }

        return $join_options;
    }


    //

    public function relationalQueryAsBasicSqlQueryBuilder(
        AbstractDatasetSelectHandle $select_handle,
        ?int $relationship_id = null,
        array $join_options = []
    ): BasicSqlQueryBuilder {

        $join_options = $this->transferOptions($join_options);

        $fetch_query_builder = new FetchQueryBuilder(
            select_handle: $select_handle,
            model: $select_handle->getModel(),
            relationship_reference: $relationship_id,
            selection: AssortmentEnum::INCLUDE,
            node_key: null,
            perspective_number: null,
            any_modules: null,
            join_options: $join_options
        );

        $basic_sql_query_builder = $fetch_query_builder->getAsBasicSqlQueryBuilder();
        $this->injectMainSqlQueryParts($basic_sql_query_builder);

        return $basic_sql_query_builder;
    }


    //

    public function relationalQueryAsDataServeContext(AbstractDatasetSelectHandle $select_handle, ?int $relationship_id = null): TableDatasetDataServerContext
    {

        $basic_sql_query_builder = $this->relationalQueryAsBasicSqlQueryBuilder($select_handle, $relationship_id);

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute()
        );
    }


    //

    public function getSelectiveSqlQueryBuilder(AbstractDatasetSelectHandle $select_handle, ?int $relationship_id = null, array $join_options = []): BasicSqlQueryBuilder
    {

        return ($select_handle->hasExtrinsicContainers())
            ? $this->relationalQueryAsBasicSqlQueryBuilder($select_handle, $relationship_id, $join_options)
            : $this->getFundamentalBasicSqlBuilder($select_handle);
    }


    //

    public function getSingleByUniqueContainer(
        AbstractDatasetSelectHandle $select_handle,
        string $container_name,
        int|string|float $field_value,
        bool $throw_not_found = false,
        array $filter = [],
        ?array $basic_sql_query_builder_modifiers = null,
        array $join_options = []
    ): TableDatasetDataServerContext {

        $select_handle->containers->assertUniqueContainer($container_name);

        if ($filter) {
            $this->dataset->assertColumns(array_keys($filter));
        }

        $abbreviation = $this->dataset->getAbbreviation();
        $basic_sql_query_builder = $this->getSelectiveSqlQueryBuilder($select_handle, join_options: $join_options);
        $sql_server = $basic_sql_query_builder->server;
        $meta = $select_handle->getReusableSelectExpressionMetadata($container_name);
        $where_part = (SqlServer::formatColumnIdentifierSyntax($meta['column'], $meta['table_reference'] ?? null) . " = ?");
        $basic_sql_query_builder->where($where_part, params: [$field_value]);

        if ($filter) {

            $condition_group = ConditionGroup::fromArray($filter);
            $basic_sql_query_builder->whereCondition(
                $condition_group,
                fallback_condition_data: [
                    'abbreviation' => $abbreviation,
                    'parameterize' => true
                ],
                params: array_values($filter)
            );
        }

        if ($basic_sql_query_builder_modifiers) {
            Common::applyModifiers($basic_sql_query_builder, $basic_sql_query_builder_modifiers);
        }

        $result = $this->executeHandle($basic_sql_query_builder);

        if ($throw_not_found && $result->count() === 0) {
            throw new EntryNotFoundException(sprintf(
                "No entry was found where field value of container \"%s\" would match \"%s\"",
                $container_name,
                $field_value
            ));
        }

        return $this->initDataServerContext($select_handle, $result);
    }


    //

    public function getSingleByPrimaryContainer(
        AbstractDatasetSelectHandle $select_handle,
        string|int|float $primary_container_value,
        bool $throw_not_found = false,
        array $join_options = []
    ): TableDatasetDataServerContext {

        return $this->getSingleByUniqueContainer(
            $select_handle,
            $this->dataset->getPrimaryContainerName(),
            $primary_container_value,
            join_options: $join_options
        );
    }


    //

    public function getAll(AbstractDatasetSelectHandle $select_handle): TableDatasetDataServerContext
    {

        $basic_sql_query_builder = $this->getSelectiveSqlQueryBuilder($select_handle);

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute()
        );
    }


    //

    public function list(
        AbstractDatasetSelectHandle $select_handle,
        ?EnhancedPropertyModel $action_params = null,
        ?EnhancedPropertyModel $filter_params = null,
        ?array $basic_sql_query_builder_modifiers = null,
        array $join_options = [],
    ): TableDatasetDataServerContext {

        if (!$action_params) {
            $action_params = self::getModelForActionType('read');
        }

        [$original_offset, $calculated_offset] = $this->validateActionParamsBeforeResult($action_params);
        $params = $action_params->getValues([
            'order',
            'relationship',
            'node_key',
            'limit',
            'offset',
            'sort',
            'search_query',
            'search_query_mark',
            'count'
        ]);

        // Translate string order value into an enum value.
        if (isset($params['order'])) {
            $params['order'] = SortByComponent::standardOrderStringToEnum(
                $params['order'],
                case_sensitive: false
            );
        }

        if (isset($params['relationship']) || array_key_exists('relationship', $params)) {
            $params['relationship_reference'] = $params['relationship'];
            unset($params['relationship']);
        }

        if (isset($params['node_key'])) {
            $params['node_key'] = new RelationshipNodeKey($params['node_key']);
        }

        if ($filter_params) {
            $params['filter_params'] = $filter_params->toConditionGroup();
        }

        $model = $select_handle->getModel();

        if ($params['search_query_mark'] && isset($params['search_query'])) {
            $this->markSearchMatches($model, $params['search_query'], $select_handle);
        }

        unset($params['search_query_mark'], $params['count']);

        $fetch_query_builder_params = [
            'select_handle' => $select_handle,
            'model' => $model,
            ...$params,
        ];
        $join_options = $this->transferOptions($join_options);
        $fetch_query_builder_params['join_options'] = $join_options;

        $fetch_query_builder = new FetchQueryBuilder(...$fetch_query_builder_params);
        $basic_sql_query_builder = $fetch_query_builder->getAsBasicSqlQueryBuilder();
        $this->injectMainSqlQueryParts($basic_sql_query_builder);

        if ($basic_sql_query_builder_modifiers) {
            Common::applyModifiers($basic_sql_query_builder, $basic_sql_query_builder_modifiers);
        }

        #temp
        #pre($basic_sql_query_builder->getFullQueryString(format: true));
        #end temp

        $no_limit_count = $basic_sql_query_builder->getNoLimitCount();
        $this->validateActionParamsAfterResult($action_params, $original_offset, $no_limit_count);

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute(),
            $no_limit_count,
            $action_params,
            $model,
            $filter_params
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

        $match_check_query_params = $this->dataset->buildStandardUniqueCase(
            $model,
            parameterize: true,
            exclude_prime: $exclude_prime,
            model_for_default_case: $model_for_default_case,
            required_when_not_available: $required_when_not_available,
            compare_main_unique_case_participants: $compare_main_unique_case_participants
        );

        if (!$match_check_query_params) {
            return null;
        }

        [$condition_group, $execution_params] = $match_check_query_params;

        if ($condition_group->count() === 0) {
            return null;
        }

        $basic_sql_query_builder = $this->getFundamentalBasicSqlBuilder($select_handle)->whereCondition($condition_group, params: $execution_params);
        $sql_result = $basic_sql_query_builder->execute();

        if ($sql_result->count() === 0) {
            return null;
        }

        return (!$return_array)
            ? $sql_result
            : $sql_result->getFirst();
    }


    //

    public function findMatches(
        AbstractDatasetSelectHandle $select_handle,
        ModelCollection $model_collection,
        bool $iterate_as_array = false,
        bool $use_rcte = true,
        ?array $models_for_default_case = null,
        bool $required_when_not_available = true
    ): ?iterable {

        if (!$model_collection->count()) {
            return null;
        }

        $all_execution_params = [];
        $root_condition_group = new ConditionGroup();
        $i = 0;

        foreach ($model_collection as $model_name => $model) {

            $params = $this->dataset->buildStandardUniqueCase(
                $model,
                parameterize: true,
                rcte_id: (($use_rcte) ? $i : null),
                model_for_default_case: (($models_for_default_case && isset($models_for_default_case[$model_name]))
                    ? $models_for_default_case[$model_name]
                    : null),
                required_when_not_available: $required_when_not_available
            );

            if ($params) {

                [$condition_group, $execution_params] = $params;
                $all_execution_params = [...$all_execution_params, ...$execution_params];

                $root_condition_group->add($condition_group, NamedOperatorsEnum::OR);
            }

            $i++;
        }

        if ($root_condition_group->count() === 0) {
            return null;
        }

        $basic_sql_query_builder = $this->getFundamentalBasicSqlBuilder($select_handle)->whereCondition($root_condition_group, params: $all_execution_params);

        /* Add recursive common table expression to map each distinct condition group to model (or the info that it is looking for). */
        if ($use_rcte) {
            $basic_sql_query_builder->addBasicRecursiveCteComponents($model_collection->count());
        }

        $sql_result = $basic_sql_query_builder->execute();

        return ($sql_result->count() !== 0)
            ? $sql_result
            : null;
    }


    //

    public function getByCondition(AbstractDatasetSelectHandle $select_handle, Condition $condition): TableDatasetDataServerContext
    {

        $abbreviation = $this->dataset->getAbbreviation();
        $basic_sql_query_builder = $this->getSelectiveSqlQueryBuilder($select_handle);
        $basic_sql_query_builder->whereCondition(
            $condition,
            fallback_condition_data: [
                'abbreviation' => $abbreviation,
            ]
        );

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute()
        );
    }


    //
    // $rcte_iterator_count - Used when `$use_rcte` is set to `true`

    public function getByConditionGroup(
        AbstractDatasetSelectHandle $select_handle,
        ConditionGroup $condition_group,
        bool $use_rcte = false,
        int $rcte_iterator_count = 1,
        bool $parameterize = false,
        array $join_options = []
    ): TableDatasetDataServerContext {

        $abbreviation = $this->dataset->getAbbreviation();
        $basic_sql_query_builder = $this->getSelectiveSqlQueryBuilder($select_handle, join_options: $join_options);
        $params = (!$parameterize)
            ? []
            : $condition_group->getValues(unique: false);
        $basic_sql_query_builder->whereCondition(
            $condition_group,
            fallback_condition_data: [
                'abbreviation' => $abbreviation
            ],
            params: $params
        );

        if ($use_rcte) {
            $basic_sql_query_builder->addBasicRecursiveCteComponents($rcte_iterator_count);
        }

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute()
        );
    }


    // Filters entries by values in a given container

    public function filterByValues(AbstractDatasetSelectHandle $select_handle, string $container_name, array $field_values): TableDatasetDataServerContext
    {

        $this->dataset->containers->assertContainerExistence($container_name);
        $abbreviation = $this->dataset->getAbbreviation();
        $basic_sql_query_builder = $this->getSelectiveSqlQueryBuilder($select_handle);

        $basic_sql_query_builder->where(
            sprintf(
                "%s IN (%s)",
                SqlServer::formatColumnIdentifierSyntax($container_name, $abbreviation),
                implode(',', array_fill(0, count($field_values), '?'))
            ),
            params: $field_values
        );

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute()
        );
    }


    // Filters entries by data pairs
    // Efficiency function. For more complex clauses use `getByConditionGroup`
    // $operator - Operator that will be used to join the pairs

    public function filterByPairs(
        AbstractDatasetSelectHandle $select_handle,
        array $data,
        NamedOperatorsEnum $operator = NamedOperatorsEnum::AND
    ): TableDatasetDataServerContext {

        if (!$data) {
            throw new \ValueError("Argument #2 (\$data) must not be empty");
        }

        $container_names = array_keys($data);
        $this->dataset->containers->containersExist($container_names, throw: true);
        $abbreviation = $this->dataset->getAbbreviation();
        $basic_sql_query_builder = $this->getSelectiveSqlQueryBuilder($select_handle);

        foreach ($data as $container_name => $value) {
            $basic_sql_query_builder->where(
                SqlServer::formatColumnIdentifierSyntax($container_name, $abbreviation) . ' = ?',
                $operator,
                params: [$value]
            );
        }

        return $this->initDataServerContext(
            $select_handle,
            $basic_sql_query_builder->execute()
        );
    }


    //
    // Puts an assertion around the execute function

    public function executeHandle(BasicSqlQueryBuilderWithExecution $builder): Result
    {

        try {
            $result = $builder->execute();
        } catch (\mysqli_sql_exception $exception) {
            throw new FetchException("Failed to fetch", previous: $exception);
        }

        return $result;
    }
}
