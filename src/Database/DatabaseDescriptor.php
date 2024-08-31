<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Rules\NumberFormattingRule;
use LWP\Components\Rules\FormattingRule;

class DatabaseDescriptor implements DatabaseDescriptorInterface
{
    public function __construct(
        public readonly Database $database
    ) {

    }


    //

    public function getSupportedFormattingRulesMap(): array
    {

        $rules_namespace = 'LWP\Components\Rules';
        $syntax_builders_namespace = (__NAMESPACE__ . '\SyntaxBuilders');

        return [
            ($rules_namespace . '\NumberFormattingRule') => ($syntax_builders_namespace . '\FormatSyntaxBuilder'),
            ($rules_namespace . '\StringTrimFormattingRule') => ($syntax_builders_namespace . '\StringTrimSyntaxBuilder'),
            ($rules_namespace . '\DateTimeFormattingRule') => ($syntax_builders_namespace . '\DateFormatSyntaxBuilder'),
            ($rules_namespace . '\ConcatFormattingRule') => ($syntax_builders_namespace . '\ConcatSyntaxBuilder'),
            ($rules_namespace . '\CalcFormattingRule') => ($syntax_builders_namespace . '\CalcSyntaxBuilder'),
        ];
    }


    //

    public function getSyntaxBuilderClassName(string $formatting_rule_class_name): ?string
    {

        $map = $this->getSupportedFormattingRulesMap();

        return ($map[$formatting_rule_class_name] ?? null);
    }


    //

    public function getSupportedFormattingRules(): array
    {

        return array_keys($this->getSupportedFormattingRulesMap());
    }


    //

    public function isSupportedFormattingRule(string $formatting_rule_class_name): bool
    {

        return isset($this->getSupportedFormattingRulesMap()[$formatting_rule_class_name]);
    }


    //

    public function getSetterFormattingRuleMap(): array
    {

        return [
            (DateTimeFormattingRule::class) => (static function (): FormattingRule {
                return new DateTimeFormattingRule([
                    'format' => 'Y-m-d H:i:s',
                ]);
            }),
            (NumberFormattingRule::class) => (static function (): FormattingRule {
                return new NumberFormattingRule([
                    'fractional_part_length' => 2,
                    'fractional_part_separator' => '.',
                    'fractional_part_ignore_zeros' => true,
                    'integer_part_group_separator' => null,
                ]);
            }),
        ];
    }


    //

    public function getSetterFormattingRuleByClassName(string $class_name): ?FormattingRule
    {

        $formatting_rule_map = $this->getSetterFormattingRuleMap();

        if (!isset($formatting_rule_map[$class_name])) {
            return null;
        }

        // Unpack formatting rule by calling the closure.
        return $formatting_rule_map[$class_name]();
    }


    //

    public function getSetterFormattingRuleForDataType(string $data_type): ?FormattingRule
    {

        $formatting_rule_map = $this->getSetterFormattingRuleMap();

        return match ($data_type) {
            // Data store date-time format.
            // https://dev.mysql.com/doc/refman/en/datetime.html
            'datetime' => $formatting_rule_map[DateTimeFormattingRule::class](), // Unpack.
            // Data store number format (primarily for decimal, but also for numbers in varchar).
            // https://dev.mysql.com/doc/refman/en/precision-math-decimal-characteristics.html
            'number' => $formatting_rule_map[NumberFormattingRule::class](), // Unpack.
            default => null,
        };
    }
}
