<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Common\Enums\AssortmentEnum;
use LWP\Components\Datasets\Relationships\RelationshipNodeKey;
use LWP\Components\Datasets\Relationships\RelationshipPerspective;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Relationships\RelationshipNodeStorageInterface;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Database\Server as SqlServer;

class DatasetJoinSyntaxBuilder
{
    public readonly Relationship $relationship;
    public readonly ?RelationshipNodeStorageInterface $node_dataset;
    public readonly ?string $node_dataset_abbreviation;


    public function __construct(
        public readonly RelationshipPerspective $perspective,
        public readonly RelationshipPerspective|(RelationshipNodeStorageInterface&DatasetInterface) $other_perspective,
        public readonly ?RelationshipNodeKey $node_key = null,
        public readonly AssortmentEnum $selection = AssortmentEnum::INCLUDE,
        public readonly array $any_dataset_field_values = [],
        public readonly ?string $custom_node_dataset_abbreviation = null,
        // Supported: "default"
        public readonly array $options = [],
        public readonly array $taken_abbreviations = []
    ) {

        $this->relationship = $perspective->relationship;
        $this->node_dataset = $this->relationship->node_dataset;
        $this->node_dataset_abbreviation = ($custom_node_dataset_abbreviation ?? $this->node_dataset?->getAbbreviation($taken_abbreviations));
    }


    //

    public function getJoinPart(): array
    {

        $sql_str = ($this->selection === AssortmentEnum::INCLUDE)
            ? ""
            : "LEFT ";
        $sql_str .= "JOIN ";
        $other_perspective_node_store = ($this->other_perspective instanceof RelationshipNodeStorageInterface);

        // Other perspective is NOT a node store.
        $other_perspective_dataset = (!$other_perspective_node_store)
            ? $this->other_perspective->dataset
            // Node store is also a dataset.
            : $this->other_perspective;

        $is_node = (
            $this->relationship->isNode()
            // The main join dataset should not be the node dataset, eg. when joining directly to the node dataset.
            && $other_perspective_dataset !== $this->node_dataset
        );

        if ($is_node) {
            $sql_str .= "(";
        }

        $join_dataset_abbreviation = (!$other_perspective_node_store)
            ? $other_perspective_dataset->getAbbreviation($this->taken_abbreviations)
            : $this->node_dataset_abbreviation;
        $sql_str .= SqlServer::formatTableIdentifierSyntax(
            $other_perspective_dataset->getDatasetName(),
            $join_dataset_abbreviation
        );

        if ($is_node) {
            $sql_str .= " CROSS JOIN "
                . SqlServer::formatTableIdentifierSyntax($this->node_dataset->getDatasetName(), $this->node_dataset_abbreviation)
                . ")";
        }

        return [$sql_str, $join_dataset_abbreviation];
    }


    //

    public function getOnPart(): array
    {

        $string = "ON ";
        $conditions = "";
        $params = [];

        // Node dataset.
        if ($this->relationship->isNode()) {

            $node_dataset = $this->relationship->node_dataset;
            $node_dataset_abbr = $this->node_dataset_abbreviation;

            // It is a store node dataset.
            if ($node_dataset_is_store = ($node_dataset instanceof RelationshipNodeStorageInterface)) {

                // Add in store specific "relationship" and "length" statements.
                $conditions .= (SqlServer::formatColumnIdentifierSyntax($node_dataset->getRelationshipFieldName(), $node_dataset_abbr) . " = ?");
                $params[] = $this->relationship->id;
                $conditions .= (" AND " . SqlServer::formatColumnIdentifierSyntax($node_dataset->getRelationshipLengthFieldName(), $node_dataset_abbr) . " = ?");
                $params[] = $this->relationship->length;
            }

            // Further statements will be added below.
            if (!empty($conditions)) {
                $conditions .= " AND ";
            }

            // Other perspective is NOT a node store. Having a node dataset, it implies that it will need to join 2 columns.
            if (!($this->other_perspective instanceof RelationshipNodeStorageInterface)) {

                /* Native relationship node store vs. custom node store. */

                $node_dataset_field_1 = ($node_dataset_is_store)
                    ? $node_dataset->getKeyContainerNameByPosition($this->perspective->position)
                    : $this->perspective->container_name;

                $node_dataset_field_2 = ($node_dataset_is_store)
                    ? $node_dataset->getKeyContainerNameByPosition($this->other_perspective->position)
                    : $this->other_perspective->container_name;

                $node_dataset_value_1 = ($node_dataset_is_store)
                    ? $this->perspective->container_name
                    : $this->perspective->dataset->getPrimaryContainerName();

                $node_dataset_value_2 = ($node_dataset_is_store)
                    ? $this->other_perspective->container_name
                    : $this->other_perspective->dataset->getPrimaryContainerName();

                $conditions .= (SqlServer::formatColumnIdentifierSyntax(
                    $node_dataset_field_1,
                    $node_dataset_abbr
                ) . " = " . SqlServer::formatColumnIdentifierSyntax(
                    $node_dataset_value_1,
                    $this->perspective->getAbbreviation()
                ));

                $conditions .= (" AND " . SqlServer::formatColumnIdentifierSyntax(
                    $node_dataset_field_2,
                    $node_dataset_abbr
                ) . " = " . SqlServer::formatColumnIdentifierSyntax(
                    $node_dataset_value_2,
                    $this->other_perspective->getAbbreviation($this->taken_abbreviations)
                ));

                if ($this->perspective->is_any) {

                    $conditions .= (" AND " . SqlServer::formatColumnIdentifierSyntax(
                        $node_dataset->getAnyContainerNameByPosition($this->perspective->position),
                        $node_dataset_abbr
                    ) . " = ?");
                    $params[] = $this->any_dataset_field_values[$this->perspective->position];
                }

                if ($this->other_perspective->is_any) {

                    $conditions .= (" AND " . SqlServer::formatColumnIdentifierSyntax(
                        $node_dataset->getAnyContainerNameByPosition($this->other_perspective->position),
                        $node_dataset_abbr
                    ) . " = ?");
                    $params[] = $this->any_dataset_field_values[$this->other_perspective->position];
                }

                if (!empty($this->options['default']) && $this->options['default'] === true) {

                    $conditions .= (" AND " . SqlServer::formatColumnIdentifierSyntax(
                        'is_default',
                        $node_dataset_abbr
                    ) . " > 0");
                }

                // Other perspective is a node store.
            } else {

                // To a single node dataset column.
                $conditions .= (SqlServer::formatColumnIdentifierSyntax(
                    $node_dataset->getKeyContainerNameByPosition($this->perspective->position),
                    $node_dataset_abbr
                ) . " = " . SqlServer::formatColumnIdentifierSyntax(
                    $this->perspective->container_name,
                    $this->perspective->getAbbreviation()
                ));
            }

            // No node dataset - related column.
        } else {

            $conditions .= SqlServer::formatColumnIdentifierSyntax(
                $this->perspective->container_name,
                $this->perspective->getAbbreviation($this->taken_abbreviations),
            ) . " = " . SqlServer::formatColumnIdentifierSyntax(
                $this->other_perspective->container_name,
                $this->other_perspective->dataset->getAbbreviation($this->taken_abbreviations),
            );
        }

        // Node key plays its part with related columns as well as matrixes.
        if ($this->node_key) {

            for ($c = 1; $c <= $this->relationship->length; $c++) {

                // Won't include key for the main perspective, because that would mean that it's pointing to a single row.
                if ($this->perspective->position !== $c) {

                    // Accept single part node keys, when relationship length is 2 parts.
                    $node_key_value = $this->node_key->get($c, ($this->relationship->length === 2));

                    if ($node_key_value) {

                        if (isset($node_dataset)) {

                            $container_name = $node_dataset->getKeyContainerNameByPosition($c);
                            $abbreviation = $node_dataset_abbr;

                        } else {

                            $container_name = (!$this->perspective->isContainerPrimary())
                                ? $this->other_perspective->container_name
                                : $this->other_perspective->dataset->getPrimaryContainerName();
                            $abbreviation = $this->other_perspective->dataset->getAbbreviation($this->taken_abbreviations);
                        }

                        $conditions .= (" AND " . SqlServer::formatColumnIdentifierSyntax($container_name, $abbreviation) . " = ?");
                        $params[] = $node_key_value;
                    }
                }
            }
        }

        $string .= $conditions;

        return [$string, $params];
    }


    //

    public function getFull(): array
    {

        [$string, $join_dataset_abbreviation] = $this->getJoinPart();
        [$on_part_string, $params] = $this->getOnPart();
        $string .= (" " . $on_part_string);

        return [$string, $params, $join_dataset_abbreviation];
    }


    //

    public function getFullString(): string
    {

        return $this->getFull()[0];
    }
}
