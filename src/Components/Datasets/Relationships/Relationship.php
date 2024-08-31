<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Common\Enums\ParityEnum;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Exceptions\AmbiguousDatasetException;
use LWP\Components\Datasets\Relationships\RelationshipNodeStorageInterface;
use LWP\Components\Datasets\Relationships\Exceptions\MissingRelationshipNodeStoreException;
use LWP\Components\Datasets\Attributes\AnyDatasetAttribute;
use LWP\Components\Datasets\Relationships\Exceptions\RelationshipLengthException;

class Relationship implements Indexable, Collectable
{
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;


    public readonly int $length;
    public readonly array $type_codes;


    public function __construct(
        public readonly string $name,
        public readonly int $id,
        protected array $dataset_array,
        public readonly int $type_code,
        public readonly array $column_list,
        public readonly (RelationshipNodeStorageInterface&DatasetInterface)|null $node_dataset = null
    ) {

        $this->length = count($dataset_array);
        $column_list_count = count($column_list);

        if ($this->length !== $column_list_count) {
            throw new RelationshipLengthException(sprintf(
                "Column list length (%d) must match the relationship length (%d).",
                $column_list_count,
                $this->length
            ));
        }

        if ($this->length > 2 && !$node_dataset) {
            throw new MissingRelationshipNodeStoreException(sprintf(
                "When relationship length is above 2 (%d given), node dataset must be provided.",
                $this->length
            ));
        }

        $this->dataset_array = array_values($dataset_array);
        $type_codes = self::parseNumericName($type_code);

        if ($type_codes[0][2] === true) {
            throw new \OutOfBoundsException(sprintf(
                "Illegal digit at position 2 in numeric name %s: first part cannot nominate an \"any\" module scope.",
                $type_code
            ));
        }

        $this->type_codes = $type_codes;
    }


    //

    public function getDatasetArray(): array
    {

        return $this->dataset_array;
    }


    //

    public function isNode(): bool
    {

        return ($this->node_dataset !== null);
    }


    // Tells if this relationship is ambiguous, meaning that one or more datasets match.

    public function isAmbiguous(): bool
    {

        $counters = [];

        foreach ($this->dataset_array as $dataset_info) {

            // If at least one dataset is marked as "any", it must ambiguous.
            if (($dataset_info instanceof AnyDatasetAttribute)) {
                return true;
            }

            $dataset_name = $dataset_info[0];

            if (isset($counters[$dataset_name])) {
                return true;
            }

            $counters[$dataset_name] = 1;
        }

        return false;
    }


    //

    public function isAmbiguousFor(DatasetInterface $dataset): bool
    {

        $count = 0;
        $dataset_name_search = $dataset->getDatasetName();

        foreach ($this->dataset_array as $dataset_info) {

            // If "any" was found, that counts as a match.
            if (($dataset_info instanceof AnyDatasetAttribute) || $dataset_info[0] == $dataset_name_search) {

                if ($count) {
                    return true;
                }

                $count++;
            }
        }

        return false;
    }


    //

    public function containsAny(): ?array
    {

        $result = [];

        foreach ($this->dataset_array as $index => $dataset_info) {

            if (($dataset_info instanceof AnyDatasetAttribute)) {
                $result[] = ($index + 1);
            }
        }

        return ($result ?: null);
    }


    //

    public function getIndexablePropertyList(): array
    {

        return [
            'id',
            'name',
            'length',
            'type_code',
            'is_node',
        ];
    }


    //

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        return match ($property_name) {
            'id' => $this->id,
            'name' => $this->name,
            'length' => $this->length,
            'type_code' => $this->type_code,
            'is_node' => (int)$this->isNode(),
        };
    }


    //

    public function getDataset(
        int $position,
        \Closure|DatasetInterface|null $resolution_when_any = null,
        // Whether to replace AnyDatasetAttribute with a resolved "any" scope module dataset.
        bool $save_resolved_any = false
    ): DatasetInterface {

        $position_zero_based = ($position - 1);

        if (!isset($this->dataset_array[$position_zero_based])) {
            throw new \OutOfBoundsException(sprintf(
                "No element was found at position %d",
                $position
            ));
        }

        $dataset_info = $this->dataset_array[$position_zero_based];

        if ($dataset_info instanceof AnyDatasetAttribute) {

            if (!$resolution_when_any) {

                throw new \RuntimeException(sprintf(
                    "Cannot resolve dataset at position %d for relationship \"%s\"",
                    $position,
                    $this->name
                ));

            } elseif ($resolution_when_any instanceof \Closure) {

                $dataset = $resolution_when_any();

                if (!$dataset || !($dataset instanceof DatasetInterface)) {
                    throw new \UnexpectedValueException(sprintf(
                        "Provided resolution closure must return an object instace of %s.",
                        DatasetInterface::class
                    ));
                }

            } else {

                $dataset = $resolution_when_any;
            }

            if (!$save_resolved_any) {

                return $dataset;

            } else {

                $this->dataset_array[$position_zero_based] = [
                    $dataset->getDatasetName(),
                    $dataset,
                ];
            }

            // Convert lazy dataset to full.
        } elseif (($dataset_info[1] instanceof \Closure)) {

            $this->dataset_array[$position_zero_based][1] = $dataset_info[1]();
        }

        // Get the updated dataset.
        return $this->dataset_array[$position_zero_based][1];
    }


    //

    public function assertDatasetInfoAtPosition(int $position): void
    {

        if (!isset($this->dataset_array[($position - 1)])) {
            throw new \OutOfBoundsException(
                "No element was found at position \"$position\" in the dataset info array."
            );
        }
    }


    //

    public function getDatasetNameAtPosition(int $position): string|AnyDatasetAttribute
    {

        $this->assertDatasetInfoAtPosition($position);

        $position_zero_based = ($position - 1);
        $dataset_info = $this->dataset_array[$position_zero_based];

        if (($dataset_info instanceof AnyDatasetAttribute)) {
            return $dataset_info;
        }

        if (($dataset_info[1] instanceof \Closure)) {
            // Resolve lazy dataset on purpose.
            $this->dataset_array[$position_zero_based][1] = $dataset_info[1]();
        }

        return $this->dataset_array[$position_zero_based][1]->getDatasetName();
    }


    //

    public function isAnyAtPosition(int $position): bool
    {

        $this->assertDatasetInfoAtPosition($position);

        return (($this->dataset_array[($position - 1)] instanceof AnyDatasetAttribute));
    }


    //

    public function getPerspectiveByContainerNumber(int $container_number, \Closure|DatasetInterface|null $resolution_when_any = null): RelationshipPerspective
    {

        #todo: assert
        if ($container_number < 1 || $container_number > $this->length) {
            throw new \RangeException(sprintf(
                "Container number must be between 1 and %d",
                $this->length
            ));
        }

        $container_number_zero_based = ($container_number - 1);
        $type_code_data = $this->type_codes[$container_number_zero_based];

        $dataset = $this->getDataset($container_number, $resolution_when_any);
        $container_name = $this->column_list[$container_number_zero_based];

        // Normalize column name - when "null", convert to dataset's primary container name.
        if (!$container_name) {
            $container_name = $dataset->getPrimaryContainerName();
        }

        return new RelationshipPerspective(
            relationship: $this,
            dataset: $dataset,
            container_name: $container_name,
            type_code: $type_code_data[0],
            is_any: $type_code_data[2],
            position: $container_number,
        );
    }


    //

    public function getPerspectiveByContainerLetter(string $container_letter): RelationshipPerspective
    {

        $map = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
        ];

        $container_letter = strtolower($container_letter);

        if (!isset($map[$container_letter])) {
            throw new \OutOfBoundsException(sprintf("Unrecognized container letter \"%s\".", $container_letter));
        }

        return $this->getPerspectiveByContainerNumber($map[$container_letter]);
    }


    //

    public function getPerspectiveByFirstNodeType(RelationshipNodeTypeEnum $node_type_name): RelationshipPerspective
    {

        foreach ($this->type_codes as $index => $type_code_data) {

            if ($type_code_data && $type_code_data[1] == $node_type_name->name) {
                return $this->getPerspectiveByContainerNumber($index + 1);
            }
        }

        throw new NotFoundException("No dataset was found which can be used to create a perspective.");
    }


    //

    public function getPerspectiveByFirstMany(): RelationshipPerspective
    {

        return $this->getPerspectiveByFirstNodeType(RelationshipNodeTypeEnum::MANY);
    }


    //

    public function getPerspectiveByFirstOne(): RelationshipPerspective
    {

        return $this->getPerspectiveByFirstNodeType(RelationshipNodeTypeEnum::ONE);
    }


    //
    /*
     * @var $strict - whether "any" datasets should be ignored.
     * @var $first_when_ambiguous - when relationship is ambiguous, just get the first dataset.
     */

    public function getPerspectiveByDataset(DatasetInterface $dataset, bool $strict = false, bool $first_when_ambiguous = false): RelationshipPerspective
    {

        $dataset_name = $dataset->getDatasetName();

        if ((!$first_when_ambiguous || $strict) && $this->isAmbiguousFor($dataset)) {
            throw new AmbiguousDatasetException(sprintf(
                "Dataset \"%s\" is ambiguous in relationship \"%s\".",
                $dataset_name,
                $this->name
            ));
        }

        foreach ($this->dataset_array as $index => $dataset_info) {

            $is_any = ($dataset_info instanceof AnyDatasetAttribute);

            // It is an "any" dataset, or a matching dataset name was found.
            if (($is_any && !$strict) || (!$is_any && $dataset_name == $dataset_info[0])) {
                return $this->getPerspectiveByContainerNumber(($index + 1), $dataset);
            }
        }

        throw new NotFoundException(sprintf(
            "No dataset was found which can be used to create a perspective in relationship %d",
            $this->id
        ));
    }


    //

    public function getPerspectiveFromBuildOptions(array $build_options, ?DatasetInterface $dataset = null): RelationshipPerspective
    {

        $has_perspective = !empty($build_options['perspective']);

        if (!$has_perspective && !$dataset) {
            throw new \ValueError("Dataset is required when perspective is not defined in build options");
        }

        if ($has_perspective) {

            $perspective = $build_options['perspective'];

            if ((!is_int($perspective) && !is_string($perspective)) || !ctype_digit((string)$perspective)) {
                throw new \ValueError("Perspective must be a numeric value");
            }

            return $this->getPerspectiveByContainerNumber((int)$perspective);

        } else {

            return $this->getPerspectiveByDataset($dataset);
        }
    }


    //

    public function getTheOtherPerspectiveFromBuildOptions(array $build_options, ?DatasetInterface $dataset = null): RelationshipPerspective
    {

        if (empty($build_options['which']) && !$dataset) {
            throw new \Exception("Dataset is required when \"which\" element is not defined in build options");
        }

        if (!empty($build_options['which'])) {

            $which = $build_options['which'];

            if ((!is_int($which) && !is_string($which)) || !ctype_digit((string)$which)) {
                throw new \Exception("\"which\" element must be a numeric value");
            }

            return $this->getPerspectiveByContainerNumber((int)$which, $dataset);

        } else {

            return $this->getPerspectiveByDataset($dataset)->getTheOtherPerspective();
        }
    }


    //

    public function getTheOtherPositionFromBuildOptions(array $build_options, ?DatasetInterface $dataset = null): int
    {

        if (empty($build_options['which']) && !$dataset) {
            throw new \Exception("Dataset is required when \"which\" element is not defined in build options");
        }

        if (empty($build_options['which'])) {

            $main_perspective = (empty($build_options['perspective']))
                ? $this->getPerspectiveByDataset($dataset)
                : $this->getPerspectiveByContainerNumber($build_options['perspective']);

            $the_other_position = $main_perspective->getTheOtherPosition();

        } else {

            $the_other_position = $build_options['which'];
        }

        return $the_other_position;
    }


    //

    public static function convertNodeTypeNameToInteger(RelationshipNodeTypeEnum $node_type_name): int
    {

        return ($node_type_name == RelationshipNodeTypeEnum::MANY)
            ? 2 // Many
            : 1; // One
    }


    //

    public static function convertTypeNumberToTypeName(int $node_type_code): ?string
    {

        return match ($node_type_code) {
            1 => RelationshipNodeTypeEnum::ONE->name,
            2 => RelationshipNodeTypeEnum::MANY->name,
            default => null
        };
    }


    //

    public function getOppositeDataset(DatasetInterface $dataset): ?DatasetInterface
    {

        return ($dataset->getDatasetName() == $this->dataset1->getDatasetName())
            ? $this->dataset2
            : $this->dataset1;
    }


    //

    public function isAllOnes(): bool
    {

        $one_name = RelationshipNodeTypeEnum::ONE->name;

        foreach ($this->type_codes as $index => $type_code_data) {

            if ($type_code_data && $type_code_data[1] != $one_name) {
                return false;
            }
        }

        return true;
    }


    // Parses relationship's numeric name.

    public static function parseNumericName(int $numeric_name): array
    {

        if ($numeric_name < 0) {
            throw new \RangeException(
                "Numeric name cannot be negative."
            );
        }

        $numeric_name_string = (string)$numeric_name;
        $numeric_name_length = strlen($numeric_name_string);

        if ($numeric_name_length !== 10) {
            throw new \LengthException(sprintf(
                "Numeric name must consist of 10 characters, %d given.",
                $numeric_name_length
            ));
        }

        $parts = str_split($numeric_name_string, 2);
        $result = [];
        $empty_part_found = false;

        foreach ($parts as $i => $part) {

            $type_code = (int)$part[0];
            $any_code = (int)$part[1];
            $odd_digit_position = (($i + 1) * 2 - 1);
            $even_digit_position = (($i + 1) * 2);

            if ($empty_part_found && ($type_code !== 0 || $any_code !== 0)) {

                $position = ($type_code !== 0)
                    ? $odd_digit_position
                    : $even_digit_position;

                throw new \OutOfBoundsException(
                    "Illegal digit at position $position in numeric name $numeric_name: zerofill part can be succeeded by zero digits only."
                );
            }

            if ($type_code < 0 || $type_code > 2) {

                throw new \OutOfBoundsException(
                    "Illegal digit at position $odd_digit_position in numeric name $numeric_name: type code must be between 0 and 2, $type_code given."
                );
            }

            if ($type_code !== 0) {

                if ($any_code < 0 || $any_code > 1) {

                    throw new \OutOfBoundsException(
                        "Illegal digit at position $even_digit_position in numeric name $numeric_name: \"any\" module scope code must be between 0 and 1, $any_code given."
                    );
                }

                $result[] = [
                    // Numeric type code (1 or 2).
                    $type_code,
                    // Textual type code ("one" or "many").
                    self::convertTypeNumberToTypeName($type_code),
                    // Whether it's an "any" module scope.
                    ($any_code === 1)
                ];

            } else {

                if ($any_code !== 0) {

                    throw new \OutOfBoundsException(
                        "Illegal digit at position $even_digit_position in numeric name $numeric_name: zero type code cannot be succeeded by a non-zero digit."
                    );
                }

                $result[] = null;

                $empty_part_found = true;
            }
        }

        return $result;
    }


    // Yields digits from a relationship numeric name by chosen parity.

    public static function yieldNumericNameNumbers(int $numeric_name, ParityEnum $parity = ParityEnum::ODD, int $max_depth = 5): \Generator
    {

        $numeric_name_string = (string)$numeric_name;
        $depth = 1;

        for ($c = 1; $c <= 10; $c++) {

            if (($c % 2) === 0) {

                if ($parity === ParityEnum::EVEN) {
                    yield $depth++ => (int)$numeric_name_string[$c - 1];
                }

            } elseif ($parity === ParityEnum::ODD) {

                yield $depth++ => (int)$numeric_name_string[$c - 1];
            }

            if ($depth > $max_depth) {
                break;
            }
        }
    }


    // Tells if given digit denotes an "any" module scope.

    public static function isAnyScopeModuleDigit(int $digit): bool
    {

        return ($digit === 1);
    }


    //

    public static function isOne(int|string $value): bool
    {

        if (is_numeric($value)) {
            $value = (int)$value;
        }

        if (is_int($value)) {
            return ($value === 1);
        }

        return ($value === RelationshipNodeTypeEnum::ONE->name);
    }


    //

    public static function isMany(int|string $value): bool
    {

        if (is_numeric($value)) {
            $value = (int)$value;
        }

        if (is_int($value)) {
            return ($value === 2);
        }

        return ($value === RelationshipNodeTypeEnum::MANY->name);
    }
}
