<?php

declare(strict_types=1);

function getCustomVersionSchemeBuilderOptions(): array
{

    return [
        'version_number_part_defs' => [
            [
                'pos' => 1,
                'label' => 'Major',
            ], [
                'pos' => 2,
                'label' => 'Patch',
                'branch' => [
                    '0' => [
                        'min' => 1,
                    ],
                ],
            ],
        ],
        'pre_release_tag_from' => [1],
    ];
}
