<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\ComplianceComparison;

/* String */

$compliance_comparison = new ComplianceComparison('abcde');
var_dump($compliance_comparison->isEqualTo('abcde'));
var_dump($compliance_comparison->isEqualTo('ABCde'));
var_dump($compliance_comparison->contains('bcd'));
var_dump($compliance_comparison->contains('bCD'));
var_dump($compliance_comparison->startsWith('abc'));
var_dump($compliance_comparison->endsWith('cde'));

$compliance_comparison = new ComplianceComparison('abcde', case_sensitive: false);
var_dump($compliance_comparison->isEqualTo('ABCde'));
var_dump($compliance_comparison->isEqualTo('ABCdĘ'));
var_dump($compliance_comparison->contains('bCD'));
var_dump($compliance_comparison->contains('bČD'));
var_dump($compliance_comparison->startsWith('Abc'));
var_dump($compliance_comparison->startsWith('Ąbč'));
var_dump($compliance_comparison->endsWith('CdE'));
var_dump($compliance_comparison->endsWith('čDę'));

$compliance_comparison = new ComplianceComparison('abcde', case_sensitive: false, accent_sensitive: false);
var_dump($compliance_comparison->isEqualTo('ABCdĘ'));
var_dump($compliance_comparison->contains('bČD'));
var_dump($compliance_comparison->startsWith('Ąbč'));
var_dump($compliance_comparison->endsWith('čDę'));


/* Array */

$compliance_comparison = new ComplianceComparison([
    'abcde',
]);
var_dump($compliance_comparison->contains('abcde'));
var_dump($compliance_comparison->contains('ABCde'));
var_dump($compliance_comparison->startsWith('abcde'));
var_dump($compliance_comparison->startsWith('ABCde'));

$compliance_comparison = new ComplianceComparison([
    'abcde',
], case_sensitive: false);
var_dump($compliance_comparison->contains('ABCde'));
var_dump($compliance_comparison->contains('ĄBČdę'));
var_dump($compliance_comparison->startsWith('ABCde'));
var_dump($compliance_comparison->startsWith('ĄBČdę'));

$compliance_comparison = new ComplianceComparison([
    'abcde',
], case_sensitive: false, accent_sensitive: false);
var_dump($compliance_comparison->contains('ĄBČdę'));
var_dump($compliance_comparison->startsWith('ĄBČdę'));

$compliance_comparison = new ComplianceComparison([['foo']]);
var_dump($compliance_comparison->contains(['foo']));


/* Integers and Floats */

$compliance_comparison = new ComplianceComparison(123.45, strict_type: false);
var_dump($compliance_comparison->contains(45));
var_dump($compliance_comparison->contains('23'));
var_dump($compliance_comparison->contains(3.45));
var_dump($compliance_comparison->isLessThan('200'));

$compliance_comparison = new ComplianceComparison(123.45, strict_type: true);
var_dump($compliance_comparison->isLessThan(200));
