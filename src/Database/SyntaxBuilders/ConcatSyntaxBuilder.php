<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Components\Rules\ConcatFormattingRule;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;
use LWP\Database\Server as SqlServer;
use LWP\Components\Rules\Exceptions\FormatNegotiationException;

class ConcatSyntaxBuilder
{
    public const FUNCTION_NAME = 'CONCAT_WS';


    public function __construct(
        public ConcatFormattingRule $concat_formatting_rule
    ) {

    }


    // Builds the funtion syntax string.

    public function getFunctionSyntax(
        array $value,
        FormatSyntaxBuilderValueEnum $value_type = FormatSyntaxBuilderValueEnum::VALUE_TYPE,
        ?string $table_abbreviation = null
    ): string {

        if ($this->concat_formatting_rule->getShrink() === false) {
            throw new FormatNegotiationException("Concat syntax builder does always shrink");
        }

        #todo: consider adding escaping
        $formatted_value = match ($value_type) {
            FormatSyntaxBuilderValueEnum::VALUE_TYPE => array_map(fn (string $value) => ("'" . $value . "'"), $value),
            FormatSyntaxBuilderValueEnum::COLUMN => SqlServer::formatColumnList($value, $table_abbreviation),
            FormatSyntaxBuilderValueEnum::RAW => $value,
        };
        $formatted_value = implode(',', $formatted_value);

        $separator = $this->concat_formatting_rule->getSeparator();
        $this->validateSeparator($separator);

        return sprintf(
            "%s('%s',%s)",
            self::FUNCTION_NAME,
            $separator,
            $formatted_value
        );
    }


    //

    public static function validateSeparator(string $separator): true
    {

        if (
            $separator === ''
            || $separator === ' '
            // Contains spaces only
            || preg_match('#^ *$#', $separator)
        ) {
            return true;
        }

        if (strpos($separator, ' ') !== false) {
            throw new \Exception("Separator must not contain space with other characters");
        }

        return true;
    }
}
