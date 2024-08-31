<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberPartParser;
use LWP\Components\DataTypes\Custom\Number\Exceptions\UniversalNumberParserException;

$number_part_parser = new NumberPartParser("-010 000 000", [
    #'group_separator' => ' ',
    #'group_size' => 3,
    #'no_group_size_when_solid' => false,
    'allow_leading_zeros' => true,
    #'allow_extended_trailing_group' => false,
]);

var_dump($number_part_parser->getNumberPart());
print_r($number_part_parser->getGroups());
print "Separator: ";
var_dump($number_part_parser->getGroupSeparator());
print "Group count: ";
var_dump($number_part_parser->getGroupCount());
print "First group length: ";
var_dump($number_part_parser->getFirstGroupLength());
print "Contains separator? ";
var_dump($number_part_parser->containsSeparators());
print "Generic group length: ";
var_dump($number_part_parser->getGenericGroupLength());
print "Integer: ";
var_dump($number_part_parser->getInteger());
print "Is signed? ";
var_dump($number_part_parser->isSigned());
print "Is trailing group extended? ";
var_dump($number_part_parser->isTrailingGroupExtended());
print "Leading zeros length: ";
var_dump($number_part_parser->getLeadingZerosLength());
print "Digits count: ";
var_dump($number_part_parser->getDigitsCount());
print "Integer digits count: ";
var_dump($number_part_parser->getIntegerDigitsCount());
