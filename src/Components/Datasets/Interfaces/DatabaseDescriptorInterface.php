<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

interface DatabaseDescriptorInterface
{
    //

    public function getSupportedFormattingRulesMap(): array;


    //

    public function getSupportedFormattingRules(): array;


    //

    public function isSupportedFormattingRule(string $formatting_rule_class_name): bool;

}
