<?php

declare(strict_types=1);

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;

function validateCondition(Condition $condition): bool
{

    $val = $condition->value;

    return match ($condition->keyword) {
        'one' => ($val == 1),
        'two' => ($val == 2),
        'three' => ($val == 3),
        'four' => ($val == 4),
        'five' => ($val == 5),
        'six' => ($val == 6),
        'seven' => ($val == 7),
        'eight' => ($val == 8),
        'nine' => ($val == 9),
        'ten' => ($val == 10),
        'eleven' => ($val == 11),
        'twelve' => ($val == 12),
        'thirteen' => ($val == 13),
        'fourteen' => ($val == 14),
        'fifteen' => ($val == 15),
        'sixteen' => ($val == 16),
    };
}
