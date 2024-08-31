<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Common\Enums\ValidityEnum;

// Opaque "required" state.

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty(
    $relational_model,
    'prop_1',
    'string',
    relational_required: 'group_2'
);

if ($relational_property->getState() !== ValidityEnum::INVALID) {
    throw new \Error("Initial state should be invalid");
}

Demo\assert_true(
    $relational_property->isRequired() === null,
    "Required state should be set to NULL"
);
