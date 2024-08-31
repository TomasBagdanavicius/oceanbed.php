<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

interface DateTimeFormatMapInterface
{
    // Primary map should include more complex cases.

    public static function getPrimaryMap(): array;

    // A map with simple cases.

    public static function getSecondaryMap(): array;

    // Merged primary and secondary maps.

    public static function getFullMap(): array;

    // Control of how a regular string should be escaped.

    public static function escape(string $str): string;

}
