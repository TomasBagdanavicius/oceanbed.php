<?php

declare(strict_types=1);

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;

/* Run reactive matching */

$reactive_matches = [];

foreach ($definition_array as $index => $definition_data) {

    $result = $condition_group_root->reactiveMatch(
        function (Condition $condition) use ($definition_data): bool {

            return Condition::assessComparisonOperator(
                $definition_data[$condition->keyword],
                $condition->value,
                $condition->control_operator
            );
        }
    );

    if ($result === true) {
        $reactive_matches[] = $index;
    }
}

sort($reactive_matches);
#pr($reactive_matches);

/* Indexes matching */

$indexes_generator
    = $condition_group_root->getAllConditionsHavingIndexesGenerator();

while ($indexes_generator->valid()) {

    $condition = $indexes_generator->current();

    $indexes_generator->send(
        $index_tree->assessCondition($condition)
    );
}

$index_matches = $indexes_generator->getReturn();
sort($index_matches);
#pr($index_matches);
