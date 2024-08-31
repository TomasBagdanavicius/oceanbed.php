<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Model\{
    RelationalPropertyModel,
    SharedAmounts\SharedAmountCollection
};

// Invalid shared amount group used for "required" state.

$relational_model = new RelationalPropertyModel();

$relational_model->setSharedAmountGroup(
    SharedAmountCollection::fromDefinitionCollection(
        'group_1',
        DefinitionCollection::fromArray([
            'type' => 'group',
            'max_sum' => 100,
        ])
    )
);

$expected_thrown = false;

try {

    $relational_property = new RelationalProperty(
        $relational_model,
        'prop_1',
        'string',
        relational_required: 'group_1'
    );

} catch (\Exception $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected exception not thrown"
);
