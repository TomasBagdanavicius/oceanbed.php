<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Exceptions\ElementNotAllowedException;

$element_filter = function (mixed $element, int|string $key): bool {
    if ($element === 'baz') {
        return false;
    }
    return true;
};

$collection = new ArrayCollection(element_filter: $element_filter);
$collection->set('one', 'foo');
$collection->set('two', 'bar');
try {
    $collection->set('three', 'baz');
    $result = false;
} catch (ElementNotAllowedException) {
    $result = true;
}

Demo\assert_true($result, "Element \"three\" was not filterred");
