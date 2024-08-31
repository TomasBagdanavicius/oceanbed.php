<?php

declare(strict_types=1);

namespace LWP\Common\Conditions;

interface ConditionMatchableInterface
{
    //

    public function matchCondition(Condition $condition): bool;


    //

    public function matchConditionGroup(ConditionGroup $condition_group): bool;
}
