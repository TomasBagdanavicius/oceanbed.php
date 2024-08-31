<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberDataType;

var_dump(NumberDataType::TYPE_NAME);
var_dump(NumberDataType::TYPE_TITLE);
var_dump(NumberDataType::getConverterClassName());
var_dump(NumberDataType::getValidatorClassName());

if (NumberDataType::hasBuilder()) {
    var_dump(NumberDataType::getBuilderClassName());
}

var_dump(NumberDataType::getDefinition());
var_dump(NumberDataType::getSupportedConstraintClassNameList());
var_dump(NumberDataType::getSupportedDefinitionList());
