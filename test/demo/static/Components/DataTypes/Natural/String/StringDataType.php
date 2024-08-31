<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Natural\String\StringDataType;

var_dump(StringDataType::TYPE_NAME);
var_dump(StringDataType::TYPE_TITLE);
var_dump(StringDataType::getConverterClassName());
var_dump(StringDataType::getValidatorClassName());

if (StringDataType::hasBuilder()) {
    var_dump(StringDataType::getBuilderClassName());
}

var_dump(StringDataType::getPhpVariableTypeEquivalent());
var_dump(StringDataType::getDefinition());
var_dump(StringDataType::getSupportedConstraintClassNameList());
var_dump(StringDataType::getSupportedDefinitionList());
