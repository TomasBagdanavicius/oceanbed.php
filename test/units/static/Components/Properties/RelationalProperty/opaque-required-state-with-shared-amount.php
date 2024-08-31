<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Model\SharedAmounts\SharedAmountCollection;
use LWP\Components\Definitions\DefinitionCollection;

// Opaque "required" state with shared amount.

$relational_model = new RelationalPropertyModel();

$relational_model->setSharedAmountGroup(
    SharedAmountCollection::fromDefinitionCollection(
        'group_1',
        DefinitionCollection::fromArray([
            'type' => 'group',
            'required_count' => 1,
        ])
    )
);

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    relational_required: 'group_1'
);

Demo\assert_true(
    $relational_property->isRequired() === null,
    "Required should be set to null"
);
