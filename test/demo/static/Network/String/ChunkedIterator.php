<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\String\ChunkedIterator;

$str = "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora laboriosam, esse mollitia ducimus repellendus sapiente laudantium iusto corporis quibusdam culpa, in aut quos cupiditate debitis fugit eius veritatis modi? Nihil.";

$chunked_iterator = new ChunkedIterator($str);

$size = 10;

do {

    print $chunked_iterator->get($size);

    $size = rand(10, 15);

} while ($chunked_iterator->getRemainingSize());
