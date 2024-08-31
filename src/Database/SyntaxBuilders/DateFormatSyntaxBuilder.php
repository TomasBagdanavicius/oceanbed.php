<?php

declare(strict_types=1);

namespace LWP\Database\SyntaxBuilders;

use LWP\Components\Rules\DateTimeFormat;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Database\Server as SqlServer;
use LWP\Database\DateTimeFormatMap;
use LWP\Database\SyntaxBuilders\Enums\FormatSyntaxBuilderValueEnum;

class DateFormatSyntaxBuilder
{
    public const FUNCTION_NAME = 'DATE_FORMAT';


    public function __construct(
        public DateTimeFormattingRule $date_time_formatting_rule
    ) {

    }


    // Builds the funtion syntax string.

    public function getFunctionSyntax(
        string $value,
        FormatSyntaxBuilderValueEnum $value_type = FormatSyntaxBuilderValueEnum::VALUE_TYPE,
        ?string $table_abbreviation = null
    ): string {

        #todo: consider adding escaping
        $date = match ($value_type) {
            FormatSyntaxBuilderValueEnum::VALUE_TYPE => ("'" . $value . "'"),
            FormatSyntaxBuilderValueEnum::COLUMN => SqlServer::formatColumnIdentifierSyntax($value, $table_abbreviation),
            FormatSyntaxBuilderValueEnum::RAW => $value,
        };

        return sprintf(
            "%s(%s, '%s')",
            self::FUNCTION_NAME,
            $date,
            (new DateTimeFormat($this->date_time_formatting_rule, new DateTimeFormatMap()))->getFormat()
        );
    }
}
