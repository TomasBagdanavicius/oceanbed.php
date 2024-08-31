<?php

declare(strict_types=1);

namespace LWP\Common\Conditions\Enums;

enum ConditionComparisonOperatorsEnum: string
{
    case EQUAL_TO = '=';
    case NOT_EQUAL_TO = '!=';
    case LESS_THAN = '<';
    case GREATER_THAN = '>';
    case LESS_THAN_OR_EQUAL_TO = '<=';
    case GREATER_THAN_OR_EQUAL_TO = '>=';
    case CONTAINS = '*';
    case STARTS_WITH = '^=';
    case ENDS_WITH = '=$';

}
