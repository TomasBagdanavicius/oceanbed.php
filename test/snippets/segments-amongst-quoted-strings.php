<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

require_once '../../src/Autoload.php';

use LWP\Common\String\Str;

/* This concept is implemented in LWP\Common\String\EnclosedCharsIterator. */

$str = 'Lorem "ipsum" dolor \'sit\' amet.';
$offset = 0;

do {

    // found opening quote
    if ($open_quote_pos = Str::posNotPrecededByMultipleClosest($str, ['\'', '"'], '\\', $offset)) {

        $offset = 0;

        // found closing quote
        if ($close_quote_pos = Str::posNotPrecededBy($str, $open_quote_pos[1], '\\', ($open_quote_pos[0] + 1))) {

            $segment = substr($str, 0, $open_quote_pos[0]);
            var_dump($segment);

            $quoted_segment = substr($str, $open_quote_pos[0], ($close_quote_pos - $open_quote_pos[0] + 1));
            var_dump($quoted_segment);

            $str = substr($str, $close_quote_pos + 1);

            // closing quote not found
        } else {

            $offset = ($open_quote_pos[0] + 1);
        }

    } else {

        $segment = $str;
        var_dump($segment);
    }

} while ($open_quote_pos);
