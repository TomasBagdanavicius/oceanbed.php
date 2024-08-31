<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\Model\{
    RelationalPropertyModel,
    SharedAmounts\SharedAmountCollection
};
use LWP\Components\Definitions\DefinitionCollection;

// Property consistency state and state change.

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'title',
    'string',
    relational_required: 'group_1',
);

// Shared amount group "group_1" has put it into invalid state.
if ($relational_property->getState() !== ValidityEnum::INVALID) {
    throw new \Error("Initial state should be invalid");
}

// This will fix the state.
$relational_model->setSharedAmountGroup(
    SharedAmountCollection::fromDefinitionCollection(
        'group_1',
        DefinitionCollection::fromArray([
            'type' => 'group',
            'required_count' => 1,
        ])
    )
);

Demo\assert_true(
    $relational_property->getState() === ValidityEnum::VALID,
    "Expected property to be in valid state"
);
