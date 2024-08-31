<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilderWithExecution;
use LWP\Database\SyntaxBuilders\DatasetJoinSyntaxBuilder;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Datasets\Relationships\RelationshipCollection;
use LWP\Components\Datasets\Relationships\RelationshipPerspective;
use LWP\Components\Properties\BaseProperty;
use LWP\Database\TableDatasetDescriptor;
use LWP\Database\SyntaxBuilders\Exceptions\UnsupportedFormattingRuleConfigException;
use LWP\Database\Server as SqlServer;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Components\Datasets\AnyDatasetAttribute;
use LWP\Components\Datasets\Relationships\RelationshipNodeStorageInterface;
use LWP\Components\Datasets\Relationships\RelationshipNodeKey;
use LWP\Components\Rules\Exceptions\FormatNegotiationException;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Null\NullDataTypeValueContainer;
use LWP\Common\Enums\StandardOrderEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Database\TableDatasetSelectHandle;
use LWP\Database\Table;

class FetchQueryBuilder
{
    private Table $dataset;
    public readonly ?Relationship $relationship;


    public function __construct(
        public readonly TableDatasetSelectHandle $select_handle,
        public readonly BasePropertyModel $model,
        public readonly ?int $limit = null,
        public readonly ?int $offset = null,
        public readonly ?string $sort = null,
        public ?StandardOrderEnum $order = null,
        public readonly ?ConditionGroup $filter_params = null,
        public readonly ?string $search_query = null,
        public readonly null|int|Relationship $relationship_reference = null,
        public readonly AssortmentEnum $selection = AssortmentEnum::INCLUDE,
        public readonly ?RelationshipNodeKey $node_key = null,
        public readonly ?int $perspective_number = null,
        public readonly null|int|string|array $any_modules = null,
        public readonly array $join_options = []
    ) {

        if ($limit < 0) {
            throw new \ValueError(
                "Limit cannot be smaller than 0"
            );
        }

        if ($offset < 0) {
            throw new \ValueError(
                "Offset cannot be smaller than 0"
            );
        }

        $this->dataset = $select_handle->dataset;
        $is_int_relationship_reference = is_int($relationship_reference);

        if ($is_int_relationship_reference) {
            $this->relationship = $this->select_handle->dataset->database->getRelationshipById($relationship_reference);
        } elseif (!$is_int_relationship_reference) {
            $this->relationship = $relationship_reference;
        }

        // When search query is available, "asc" implies starting with the most relevant, while "desc" starting with the least relevant
        if ($search_query !== null && $search_query !== '' && !$sort) {
            $this->order = ($order === null || $order === StandardOrderEnum::DESC)
                ? StandardOrderEnum::ASC
                : $this->order = StandardOrderEnum::DESC;
        } else {
            $this->order = $order;
        }
    }


    // Given perspective number resolution for a given relationship.

    public function solvePerspectiveNumber(Relationship $relationship, int $perspective_number): RelationshipPerspective
    {

        $perspective_dataset_info = $relationship->getDatasetNameAtPosition($perspective_number);
        $is_any_dataset = ($perspective_dataset_info instanceof AnyDatasetAttribute);

        if (!$is_any_dataset && $perspective_dataset_info != $this->dataset->getDatasetName()) {

            throw new \Exception(
                "Perspective does not resemble the current dataset by the given perspective number."
            );

        } else {

            $params = [
                'container_number' => $perspective_number,
            ];

            // Perspective is pointing to an "any" container - resolve using current dataset (which will not need to be unpacked).
            if ($is_any_dataset) {
                $params['resolution_when_any'] = $this->dataset;
            }

            return $relationship->getPerspectiveByContainerNumber(...$params);
        }
    }


    //

    public function solveMainPerspective(Relationship $relationship): RelationshipPerspective
    {

        if ($this->perspective_number) {
            return $this->solvePerspectiveNumber($relationship, $this->perspective_number);
        }

        // When ambiguous, it will grab the first resembling container perspective.
        return $relationship->getPerspectiveByDataset($this->dataset, first_when_ambiguous: true);
    }


    //

    public function yieldExtrinsicProperties(): \Generator
    {

        $property_collection = $this->model->getPropertyCollection();

        foreach ($property_collection as $property) {

            if ($this->select_handle->hasExtrinsicContainer($property->property_name)) {
                yield $property;
            }
        }
    }


    //

    public function getColumnListRelationships(bool $exclude_global_relationship = false): RelationshipCollection
    {

        $relationship_collection = new RelationshipCollection();

        foreach ($this->yieldExtrinsicProperties() as $property) {

            $build_options = $this->dataset->getExtrinsicPropertyBuildOptions($property->property_name);
            $relationship = $this->dataset->loadRelationship($build_options['relationship'], 'name');

            // Exclude the class global relationship.
            if ($exclude_global_relationship && $this->relationship && $relationship->id == $this->relationship->id) {
                continue;
            }

            if (!$relationship_collection->containsKey($build_options['relationship'])) {
                $relationship_collection->set($build_options['relationship'], $relationship);
            }
        }

        return $relationship_collection;
    }


    //
    // @return relationship_name => SQL join string

    public function yieldJoinPartsForColumnListRelationships(bool $exclude_global_relationship = false): \Generator
    {

        $relationship_names_cache = [];
        $extrinsic_properties = $this->yieldExtrinsicProperties();

        foreach ($extrinsic_properties as $property) {

            $main_perspective = $this->select_handle->getPerspectiveForExtrinsicContainer($property->property_name);
            $relationship_name = $main_perspective->relationship->name;

            if (
                // Prevent duplicate relationships, eg. when multiple properties with the same relationship are used.
                !in_array($relationship_name, $relationship_names_cache)
                // Exclude class global relationship, optionally.
                && (!$exclude_global_relationship || !$this->relationship || $relationship_name !== $this->relationship->name)
            ) {

                $options = (isset($this->join_options[$relationship_name]) && is_array($this->join_options[$relationship_name]))
                    ? $this->join_options[$relationship_name]
                    : [];
                $dataset_join_syntax_builder = new DatasetJoinSyntaxBuilder(
                    perspective: $main_perspective,
                    // Always a concrete perspective (no relationship nodes), because it has requirements by nature (the column fields)
                    other_perspective: $this->select_handle->getTheOtherPerspectiveForExtrinsicContainer($property->property_name),
                    // Related column join is always an exclusion selection
                    selection: AssortmentEnum::EXCLUDE,
                    options: $options
                );
                $relationship_names_cache[] = $relationship_name;
                $parts = $dataset_join_syntax_builder->getFull();

                yield $relationship_name => [
                    'part' => $parts[0],
                    'params' => $parts[1]
                ];
            }
        }
    }


    //

    public function solveOtherPerspective(Relationship $relationship, RelationshipPerspective $main_perspective): RelationshipPerspective
    {

        $other_position = $main_perspective->getTheOtherPosition();
        $is_node = $relationship->isNode();

        if ($is_node) {

            $is_storage_interface = ($relationship->node_dataset instanceof RelationshipNodeStorageInterface);

            #todo: implement this
            $has_requirements = false;

            if ($has_requirements) {

                # Which is the correct other position?

                $other_perspective = $relationship->getPerspectiveByContainerNumber($other_position);

            } else {

                /* Getting ready to join to the relationship node (relationship node storage) dataset. Since it's a foreign dataset, the idea is to construct a relationship perspective for it, rather than assign relationship node dataset to the "other_perspective" variable and have to deal with confusions where this variable is used below. */

                $relationship_nodes_other_perspective = true;

                $type_codes = $relationship->type_codes[($other_position - 1)];

                $other_perspective = new RelationshipPerspective(
                    relationship: $relationship,
                    dataset: $relationship->node_dataset,
                    container_name: $relationship->node_dataset->getPrimaryColumnName(),
                    type_code: $type_codes[0],
                    is_any: $type_codes[2],
                    position: $other_position,
                );
            }

            // Related column.
        } else {

            $params = [
                'container_number' => $other_position,
            ];

            if ($relationship->isAnyAtPosition($other_position)) {

                if (!$any_modules) {
                    throw new \RuntimeException(sprintf("Cannot resolve dataset at position %d. Module should be provided.", $other_position));
                }

                // An integer or a digit only string translates into a module ID.
                if (is_int($any_modules) || (is_string($any_modules) && ctype_digit($any_modules))) {
                    $module_id = $any_modules;
                } elseif (is_string($any_modules)) {
                    throw new \RuntimeException("Param any_modules should be a digit only string, an integer, or an array containing module IDs.");
                    // When there is only one element in the array, use it immediatelly.
                } elseif (count($any_modules) === 1) {
                    $module_id = $any_modules[array_key_first($any_modules)];
                } elseif (empty($any_modules[$other_position])) {
                    throw new \RuntimeException(sprintf("Cannot resolve dataset at position %d. Module should be provided.", $other_position));
                } else {
                    $module_id = $any_modules[$other_position];
                }

                // Will resolve to the normalized module.
                $params['resolution_when_any'] = $this->dataset->module->module_factory->getModuleById($module_id)->dataset;
            }

            $other_perspective = $relationship->getPerspectiveByContainerNumber(...$params);
        }

        return $other_perspective;
    }


    //

    public function getClausePart(RelationshipPerspective $main_perspective, RelationshipPerspective $other_perspective): array
    {

        $sql_str = '';
        $params = [];
        $relationship = $main_perspective->relationship;

        if ($this->selection === AssortmentEnum::EXCLUDE) {

            // Exclusion selection by other perspective.
            $sql_str .= ($other_perspective->getFormattedColumnSyntax() . " IS NULL");

        } elseif ($this->selection === AssortmentEnum::INCLUDE) {

            // Related column connection.
            if (!$relationship->isNode()) {

                if (!$this->node_key) {

                    // Perspective contains related column.
                    if (!$main_perspective->isContainerPrimary()) {

                        // Main perspective controls the other perspective, eg. related column is inside main perspective.
                        $sql_str .= ($main_perspective->getFormattedColumnSyntax() . " IS NOT NULL");
                    }

                } else {

                    /* Conditional */

                    // Main perspective has related.
                    if (!$main_perspective->isContainerPrimary()) {

                        // Main perspective controls the other perspective.
                        $sql_str .= $main_perspective->getFormattedColumnSyntax() . " = ?";
                        $params[] = $this->node_key->get($other_perspective->position, accept_single: true);

                        // Main perspective has primary.
                    } else {

                        // Point node key to the opposite.
                        $sql_str .= $other_perspective->getFormattedColumnSyntaxWithPrimary() . " = ?";
                        $params[] = $this->node_key->get($other_perspective->position, accept_single: true);
                    }
                }
            }
        }

        return [$sql_str, $params];
    }


    //

    public function isGrouped(Relationship $relationship, RelationshipPerspective $main_perspective, RelationshipPerspective $other_perspective): bool
    {

        $main_perspective_type_code = $main_perspective->type_code;

        #review: work in progress - for now, longer relationships with "many" position
        if ($relationship->length > 2 && $main_perspective_type_code === 2) {
            return true;
        }

        return false;
    }


    //

    public function getAsBasicSqlQueryBuilder(): BasicSqlQueryBuilder
    {

        $dataset = $this->select_handle->dataset;
        $dataset_abbreviation = $dataset->getAbbreviation();
        $basic_sql_query_builder = (new BasicSqlQueryBuilderWithExecution($dataset->server))
            ->selectFromMetadata($this->select_handle->yieldSelectExpressionMetadataList())
            ->from($dataset->name, $dataset_abbreviation);

        #todo: manage requirements
        $has_requirements;

        // Join parts excluding the global relationship.
        $basic_sql_query_builder->joinFromGenerator(
            $this->yieldJoinPartsForColumnListRelationships(
                exclude_global_relationship: true
            )
        );

        // Global class relationship exists.
        if ($this->relationship) {

            $main_perspective = $this->solveMainPerspective($this->relationship);
            $other_perspective = $this->solveOtherPerspective($this->relationship, $main_perspective);
            $relationship_nodes_other_perspective = ($other_perspective->dataset instanceof RelationshipNodeStorageInterface);

            $dataset_join_syntax_builder = new DatasetJoinSyntaxBuilder(
                perspective: $main_perspective,
                // Here it is currently possible to provide a foreign dataset directly.
                other_perspective: ((!$relationship_nodes_other_perspective)
                    ? $other_perspective
                    : $this->relationship->node_dataset),
                node_key: $this->node_key,
                selection: $this->selection
            );

            [$join_str, $params] = $dataset_join_syntax_builder->getFull();
            $basic_sql_query_builder->join($join_str, $params);

            [$clause_str, $params] = $this->getClausePart($main_perspective, $other_perspective);

            if ($clause_str) {
                $basic_sql_query_builder->where($clause_str, params: $params);
            }

            if ($this->isGrouped($this->relationship, $main_perspective, $other_perspective)) {

                $primary_container_name = $dataset->getPrimaryContainerName();
                $basic_sql_query_builder->groupBy(SqlServer::formatColumnIdentifierSyntax($primary_container_name, $dataset_abbreviation));
            }
        }

        if ($this->filter_params) {

            $basic_sql_query_builder->whereCondition(
                $this->filter_params,
                fallback_condition_data: [
                    'abbreviation' => $this->dataset->getAbbreviation(),
                ],
            );
        }

        if ($this->search_query) {
            $this->addSearchQuery($basic_sql_query_builder, $this->search_query);
        }

        if ($this->limit) {

            $limit_and_offset_params = [
                'row_count' => $this->limit,
            ];

            if ($this->offset) {
                $limit_and_offset_params['offset'] = $this->offset;
            }

            $basic_sql_query_builder->limit(...$limit_and_offset_params);
        }

        if ($this->sort) {

            $this->model->assertPropertyName($this->sort);

            $select_expression_metadata = $this->select_handle->getReusableSelectExpressionMetadata($this->sort);

            $basic_sql_query_builder->orderBy(
                $select_expression_metadata,
                $this->order,
                // Below search query order parameters.
                priority: 30
            );
        }

        return $basic_sql_query_builder;
    }


    //

    protected function addSearchQuery(
        BasicSqlQueryBuilderWithExecution $basic_sql_query_builder,
        string $search_query
    ): void {

        $searchable_containers = $this->select_handle->containers->getSearchableContainers();
        $searchable_containers_count = count($searchable_containers);

        if ($searchable_containers_count !== 0) {

            $syntax_strings = [
                'match_string' => '',
                'like_string' => '',
                'comparison_methods' => [
                    'match' => "%$search_query%",
                    'equal_to' => $search_query,
                    'prefix' => "$search_query%",
                    'first_word' => "$search_query %",
                    'word_prefix' => "% $search_query%",
                ]
            ];
            $i = 0;

            foreach ($searchable_containers as $searchable_container_name) {

                // Related property
                if ($this->select_handle->hasExtrinsicContainer($searchable_container_name)) {

                    $property = $this->select_handle->containers->getProperty($searchable_container_name);
                    $meta = $this->select_handle->getReusableSelectExpressionMetadata($property->property_name);
                    $formatted_container_name = $basic_sql_query_builder::stringifyColumnMetadata($meta);

                } else {

                    $formatted_container_name = $basic_sql_query_builder::stringifyColumnMetadata([
                        'table_reference' => $this->select_handle->dataset->getAbbreviation(),
                        'column' => $searchable_container_name,
                    ]);
                }

                $prefix = ($i)
                    ? " OR "
                    : "";
                $begin_str = ($prefix . $formatted_container_name);
                $definition_array = $this->select_handle->containers->getDefinitionsForContainer($searchable_container_name);

                /* MySQL uses the collation with the lowest coercibility value in a pair which is being compared. Coercibility value of a column is 2 and that of a string literal is 4. When column collation is "ascii_general_ci" it wins againts a string literal, but then if that string literal contains unicode chars, conflict occurs. Explicitly appending collation reduces the coercibility value to 0. What is more, question mark identifier cannot be immediatelly succeeded by COLLATE statement. */
                $value_str = (isset($definition_array['charset']) && $definition_array['charset'] === 'ascii')
                    ? "CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_520_ci"
                    : "?";

                $syntax_strings['like_string'] .= "$begin_str LIKE $value_str";

                if (!$this->sort) {

                    $syntax_strings['match_string'] .= "$begin_str = $value_str";

                    // By the number of search query occurrences.
                    $basic_sql_query_builder->orderBy(
                        ("`count_substring_occurences`(" . $formatted_container_name . ", ?, 1, FALSE, FALSE) " . $this->order->name),
                        // Below case ordering.
                        priority: 20
                    );
                }

                $i++;
            }

            unset($i);

            $where_params = array_fill(0, $searchable_containers_count, $syntax_strings['comparison_methods']['match']);
            $basic_sql_query_builder->where(
                "(" . $syntax_strings['like_string'] . ")",
                params: $where_params
            );

            if (!$this->sort) {

                $c = 1;
                $order_by_string = "CASE";
                $order_by_params = [];

                foreach ($syntax_strings['comparison_methods'] as $name => $syntax_string) {

                    if ($name === 'match') {
                        continue;
                    }

                    if ($name === 'equal_to') {
                        $order_by_string .= " WHEN {$syntax_strings['match_string']} THEN $c";
                    } else {
                        $order_by_string .= " WHEN {$syntax_strings['like_string']} THEN $c";
                    }

                    array_push(
                        $order_by_params,
                        ...array_fill(0, $searchable_containers_count, $syntax_string)
                    );

                    $c++;
                }

                $case_order = ($this->order === StandardOrderEnum::ASC)
                    ? StandardOrderEnum::DESC->name
                    : StandardOrderEnum::ASC->name;
                $order_by_string .= " ELSE $c END $case_order";

                unset($c);

                array_push(
                    $order_by_params,
                    ...array_fill(0, $searchable_containers_count, $search_query)
                );

                // Main order algorithm.
                $basic_sql_query_builder->orderBy(
                    $order_by_string,
                    priority: 10,
                    params: $order_by_params
                );

                $formatted_column_name = $basic_sql_query_builder::stringifyColumnMetadata([
                    'table_reference' => $this->select_handle->dataset->getAbbreviation(),
                    'column' => $this->select_handle->dataset->getPrimaryColumnName(),
                ]);

                $basic_sql_query_builder->orderBy(
                    "$formatted_column_name $case_order",
                    priority: 25
                );
            }
        }
    }
}
