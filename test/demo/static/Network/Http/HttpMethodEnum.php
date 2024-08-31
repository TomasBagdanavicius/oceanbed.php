<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\HttpMethodEnum;

var_dump(HttpMethodEnum::GET === HttpMethodEnum::GET);
pr(HttpMethodEnum::cases());
pr(array_map((fn (UnitEnum $unit) => $unit->name), HttpMethodEnum::cases()));
