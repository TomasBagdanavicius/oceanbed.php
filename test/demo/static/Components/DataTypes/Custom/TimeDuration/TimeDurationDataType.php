<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\TimeDuration\TimeDurationDataType;

var_dump(TimeDurationDataType::TYPE_NAME);
var_dump(TimeDurationDataType::TYPE_TITLE);
var_dump(TimeDurationDataType::getConverterClassName());
var_dump(TimeDurationDataType::getValidatorClassName());

if (TimeDurationDataType::hasBuilder()) {
    var_dump(TimeDurationDataType::getBuilderClassName());
}

var_dump(TimeDurationDataType::getDefinition());
var_dump(TimeDurationDataType::getSupportedConstraintClassNameList());
var_dump(TimeDurationDataType::getSupportedDefinitionList());
