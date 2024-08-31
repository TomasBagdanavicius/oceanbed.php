<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

class VersionScheme4PartVersioning extends VersionScheme
{
    public function __construct(
        object $structure,
        array $options,
    ) {

        parent::__construct($structure, self::getDefaultOptions());
    }


    // Gets the options for the Semantic Versioning version scheme.

    public static function getDefaultOptions(): array
    {

        return [
            'pre_release_tag' => false,
            'version_number_part_defs' => [
                [
                    'pos' => 1,
                    'label' => 'Major',
                ], [
                    'pos' => 2,
                    'label' => 'Minor',
                    'branch' => [
                        '0' => [
                            'min' => 1,
                        ],
                    ],
                ], [
                    'pos' => 3,
                    'label' => 'Build',
                    'omit_zero' => true,
                    'from' => [1],
                ], [
                    'pos' => 4,
                    'label' => 'Patch',
                    'omit_zero' => true,
                    'from' => [1],
                ],
            ],
        ];
    }


    // Gets the major part number.

    public function getMajorVersionNumber(): ?int
    {

        return $this->getVersionNumberPartByIndex(0);
    }


    // Gets the minor part number.

    public function getMinorVersionNumber(): ?int
    {

        return $this->getVersionNumberPartByIndex(1);
    }


    // Gets the build part number.

    public function getBuildVersionNumber(): ?int
    {

        return $this->getVersionNumberPartByIndex(2);
    }


    // Gets the patch part number.

    public function getPatchVersionNumber(): ?int
    {

        return $this->getVersionNumberPartByIndex(3);
    }
}
