<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

require_once(__DIR__ . '/../src/Autoload.php');






$start_time = microtime(true);

/* code snippet 1 start */



/* code snippet 1 end */

$first = (microtime(true) - $start_time);






$start_time = microtime(true);

/* code snippet 2 start */



/* code snippet 2 end */

$second = (microtime(true) - $start_time);







if ($first < $second) {

    echo "First script is quicker by " . ($second - $first) . " miliseconds";

} else {

    echo "Second script is quicker by " . ($first - $second) . " miliseconds";
}
