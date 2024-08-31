<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Model\SharedAmounts\SharedAmountCollection;
use LWP\Components\Model\SharedAmounts\MaxSumSharedAmount;

$shared_amount_collection = new SharedAmountCollection('group_1', "Group description.");

$shared_amount_collection->set('max_sum', new MaxSumSharedAmount(100));

echo "Count: ";
var_dump($shared_amount_collection->count());

/* From Definition Collection */

$definition_array = [
    'type' => 'group',
    'max_sum' => 100,
    'description' => "Group description.",
];

$definition_collection = DefinitionCollection::fromArray($definition_array);

$shared_amount_collection = SharedAmountCollection::fromDefinitionCollection('group_1', $definition_collection);

echo "From definition collection: ";
var_dump($shared_amount_collection::class);
