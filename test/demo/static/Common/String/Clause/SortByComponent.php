<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\Clause\SortByComponent;
use LWP\Common\Enums\StandardOrderEnum;

$data = [
    'name' => [
        'order' => StandardOrderEnum::ASC,
    ],
    'city' => [
        'order' => StandardOrderEnum::DESC,
    ],
    'country' => [
        #todo: does this do anything?
        'values' => 'USA',
    ],
];

$sort_by_component = new SortByComponent($data);

prl($sort_by_component->__toString());

/* From String */

pr(SortByComponent::fromString('name ASC, city DESC'));


/* Utilities */

#var_dump(SortByComponent::standardOrderStringToEnum('ASC'));
#var_dump(SortByComponent::standardOrderStringToEnum('asc', case_sensitive: false));
/* prl(SortByComponent::combineIntoString([
    'foo',
    'bar',
    'baz',
], [
    'ASC',
])); */
