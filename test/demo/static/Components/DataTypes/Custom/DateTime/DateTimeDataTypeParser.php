<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeParser;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;

/* Value Container */

$value_container = new DateTimeDataTypeValueContainer("2023-01-01 08:00:00");

/* Parser */

$datetime_parser = new DateTimeDataTypeParser($value_container);

var_dump($datetime_parser->__toString());
print "Timestamp: ";
var_dump($datetime_parser->getTimestamp());

$datetime_parser->add(new DateInterval('P10D')); // Plus period of 10 days.
var_dump($datetime_parser->format('Y-m-d'));
