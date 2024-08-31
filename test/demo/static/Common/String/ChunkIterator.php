<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\String\ChunkIterator;

$str = "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora laboriosam, esse mollitia ducimus repellendus sapiente laudantium iusto corporis quibusdam culpa, in aut quos cupiditate debitis fugit eius veritatis modi? Nihil.";


/* Basic loop. */

$chunk_iterator = new ChunkIterator($str, 10);

foreach ($chunk_iterator as $key => $chunk) {

    var_dump($chunk);
}




/* Chosen size.

$chunk_iterator = new ChunkIterator($str);

while( $chunk = $chunk_iterator->get(20) ) {

    var_dump($chunk);
}

*/


/* Random size.

$chunk_iterator = new ChunkIterator($str);

do {

    $size = rand(1, 10);

    var_dump($chunk_iterator->get($size));

    $remaining_size = $chunk_iterator->getRemainingSize();

} while( $remaining_size );

*/
