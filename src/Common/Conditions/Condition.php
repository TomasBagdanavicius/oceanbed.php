<?php

declare(strict_types=1);

namespace LWP\Common\Conditions;

use LWP\Common\Common;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\AssortmentEnum;
use LWP\Common\Exceptions\UnsupportedException;
use LWP\Components\Attributes\NoValueAttribute;
use LWP\Common\String\Str;
use LWP\Common\ComplianceComparison;

class Condition implements \Stringable
{
    public function __construct(
        public readonly string|NoValueAttribute $keyword,
        public readonly mixed $value,
        public readonly ConditionComparisonOperatorsEnum|AssortmentEnum $control_operator = ConditionComparisonOperatorsEnum::EQUAL_TO,
        // Custom data associated with this condition.
        public readonly ?array $data = null,
        // A function that alters the behavior of the stringification process.
        private ?\Closure $stringify_replacer = null,
        public readonly bool $strict_type = false,
        public readonly bool $case_sensitive = true,
        public readonly bool $accent_sensitive = true
    ) {

        $keyword_no_value = ($keyword instanceof NoValueAttribute);
        $value_no_value = ($value instanceof NoValueAttribute);

        if ($keyword_no_value && $value_no_value) {
            throw new \InvalidArgumentException("Either keyword or value must be provided.");
        } elseif (($keyword_no_value || $value_no_value) && !($control_operator instanceof AssortmentEnum)) {
            throw new \TypeError(sprintf("When either keyword or value is absent, control operator must be an instance of %s", AssortmentEnum::class));
        }
    }


    //

    public function __toString(): string
    {

        return $this->stringify(suppress_replacer: false);
    }


    //

    public function stringify(bool $suppress_replacer = false): string
    {

        if (!$suppress_replacer && $this->stringify_replacer) {

            $callback_result = ($this->stringify_replacer)($this);

            // Is allowed to return null, which means that it should fallback to internal stringification.
            if (is_string($callback_result)) {
                return $callback_result;
            }
        }

        if (($this->control_operator instanceof ConditionComparisonOperatorsEnum)) {

            return sprintf("%s %s %s", $this->keyword, $this->control_operator->value, $this->value);

        } else {

            return strtoupper($this->control_operator->name)
                . ((!($this->keyword instanceof NoValueAttribute))
                    ? (' KEYWORD ' . $this->keyword)
                    : (' VALUE ' . $this->value));
        }
    }


    //

    public function setStringifyReplacer(\Closure $stringify_replacer): void
    {

        $this->stringify_replacer = $stringify_replacer;
    }


    //

    public function unsetStringifyReplacer(): void
    {

        $this->stringify_replacer = null;
    }


    //

    public function match(string $keyword, mixed $value): bool
    {

        return ($this->matchKeyword($keyword) && $this->matchValue($value, $this->strict_type, $this->case_sensitive, $this->accent_sensitive));
    }


    //

    public function matchKeyword(string $keyword): bool
    {

        if ($this->keyword instanceof NoValueAttribute) {
            throw new \RuntimeException("Keyword is not provided");
        }

        $match_keyword = ($keyword == $this->keyword);

        return ($this->control_operator instanceof ConditionComparisonOperatorsEnum)
            ? $match_keyword
            : (
                ($this->control_operator === AssortmentEnum::INCLUDE && $match_keyword)
                || ($this->control_operator === AssortmentEnum::EXCLUDE && !$match_keyword)
            );
    }


    //

    public function matchValue(mixed $value): bool
    {

        if ($this->value instanceof NoValueAttribute) {
            throw new \RuntimeException("Value is not provided");
        }

        if ($this->control_operator instanceof ConditionComparisonOperatorsEnum) {

            return self::assessComparisonOperator(
                $value,
                $this->value,
                $this->control_operator,
                $this->strict_type,
                $this->case_sensitive,
                $this->accent_sensitive
            );

        } else {

            $match_value = ((!$this->strict_type && $value == $this->value) || ($this->strict_type && $value === $this->value));

            return (
                ($this->control_operator === AssortmentEnum::INCLUDE && $match_value)
                || ($this->control_operator === AssortmentEnum::EXCLUDE && !$match_value)
            );
        }
    }


    //

    public static function assesComparisonOperatorMiddleware(
        mixed $value1,
        mixed $value2,
        ConditionComparisonOperatorsEnum $comparison_operator,
        \Closure $callback,
        // Whether to attempt to convert given values to number when number specifix operator is used.
        bool $treat_both_values_num = true
    ): mixed {

        switch ($comparison_operator) {

            case ConditionComparisonOperatorsEnum::EQUAL_TO:
            case ConditionComparisonOperatorsEnum::NOT_EQUAL_TO:
            case ConditionComparisonOperatorsEnum::CONTAINS:
            case ConditionComparisonOperatorsEnum::STARTS_WITH:
            case ConditionComparisonOperatorsEnum::ENDS_WITH:

                return $callback($value1, $value2);

                break;

            case ConditionComparisonOperatorsEnum::LESS_THAN:
            case ConditionComparisonOperatorsEnum::GREATER_THAN:
            case ConditionComparisonOperatorsEnum::LESS_THAN_OR_EQUAL_TO:
            case ConditionComparisonOperatorsEnum::GREATER_THAN_OR_EQUAL_TO:

                $start_i = ($treat_both_values_num)
                    ? 1
                    : 2;

                for ($i = $start_i; $i <= 2; $i++) {

                    $varname = ('value' . $i);

                    if (is_string($$varname) && is_numeric($$varname)) {
                        $$varname = Common::toNumber($$varname);
                    }

                    if (!is_integer($$varname) && !is_float($$varname)) {
                        Common::throwTypeError($i, __FUNCTION__, 'integer, float, or numeric string', gettype($$varname));
                    }
                }

                return $callback($value1, $value2);

                break;

            default:

                throw new UnsupportedException(sprintf(
                    "Unsupported comparison operator \"%s\".",
                    $comparison_operator->name
                ));

                break;
        }
    }


    //

    public static function assessComparisonOperator(
        mixed $value1,
        mixed $value2,
        ConditionComparisonOperatorsEnum $comparison_operator,
        bool $strict_type = false,
        bool $case_sensitive = true,
        bool $accent_sensitive = true,
    ): bool {

        return self::assesComparisonOperatorMiddleware(
            $value1,
            $value2,
            $comparison_operator,
            function (mixed $value1, mixed $value2) use ($comparison_operator, $strict_type, $case_sensitive, $accent_sensitive): bool {

                $compliance_comparison = new ComplianceComparison($value1, $strict_type, $case_sensitive, $accent_sensitive);

                return match ($comparison_operator) {
                    ConditionComparisonOperatorsEnum::EQUAL_TO => $compliance_comparison->isEqualTo($value2),
                    ConditionComparisonOperatorsEnum::NOT_EQUAL_TO => $compliance_comparison->isNotEqualTo($value2),
                    ConditionComparisonOperatorsEnum::LESS_THAN => $compliance_comparison->isLessThan($value2),
                    ConditionComparisonOperatorsEnum::GREATER_THAN => $compliance_comparison->isGreaterThan($value2),
                    ConditionComparisonOperatorsEnum::LESS_THAN_OR_EQUAL_TO => $compliance_comparison->isLessThanOrEqualTo($value2),
                    ConditionComparisonOperatorsEnum::GREATER_THAN_OR_EQUAL_TO => $compliance_comparison->isGreaterThanOrEqualTo($value2),
                    ConditionComparisonOperatorsEnum::CONTAINS => $compliance_comparison->contains($value2),
                    ConditionComparisonOperatorsEnum::STARTS_WITH => $compliance_comparison->startsWith($value2),
                    ConditionComparisonOperatorsEnum::ENDS_WITH => $compliance_comparison->endsWith($value2),
                };
            }
        );
    }
}
