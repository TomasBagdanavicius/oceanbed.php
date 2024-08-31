<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Database\Server;

class RelationshipPerspective
{
    public function __construct(
        public readonly Relationship $relationship,
        public readonly DatasetInterface $dataset,
        public readonly string $container_name,
        public readonly int $type_code,
        public readonly bool $is_any,
        public readonly int $position
    ) {

    }


    //

    public function getTypeName(): string
    {

        return Relationship::convertTypeNumberToTypeName($this->type_code);
    }


    //

    public function isContainerPrimary(): bool
    {

        return $this->dataset->isContainerPrimary($this->container_name);
    }


    //

    public function getAbbreviation(array $taken = []): string
    {

        return $this->dataset->getAbbreviation($taken);
    }


    //

    public function getTheOtherPosition(): int
    {

        if ($this->relationship->length === 2) {

            return ($this->position === 1)
                ? 2
                : 1;

            // Next after first.
        } elseif ($this->position === 1) {

            return 2;

            // First.
        } else {

            return 1;
        }
    }


    //

    public function getTheOtherPerspective(): self
    {

        return $this->relationship->getPerspectiveByContainerNumber($this->getTheOtherPosition());
    }


    //

    public function getTheOtherDataset(): DatasetInterface
    {

        return $this->relationship->getDataset($this->getTheOtherPosition());
    }


    //

    public static function assertPerspectiveNumber(int $number): void
    {

        #todo: global assertion.
        if ($number < 1 || $number > 5) {
            throw new \OutOfRangeException(sprintf("Perspective number must be between 1 and 5, got %d.", $number));
        }
    }


    //

    public function getFormattedColumnSyntax(): string
    {

        return Server::formatColumnIdentifierSyntax($this->container_name, $this->getAbbreviation());
    }


    //

    public function getFormattedColumnSyntaxWithPrimary(): string
    {

        return Server::formatColumnIdentifierSyntax($this->dataset->getPrimaryContainerName(), $this->getAbbreviation());
    }
}
