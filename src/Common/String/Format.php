<?php

declare(strict_types=1);

namespace LWP\Common\String;

use LWP\Common\String\Str;

class Format
{
    /* Trimming */

    // Trims a single pair of matching quotes at the beginning and the end of a string.
    # An alternative "substr" solution could be considered.

    public static function trimMatchingQuotes(string $str): string
    {

        return preg_replace('/^(\'|")(.*)\\1$/u', '$2', $str);
    }


    /* Convertion */

    // Converts tag name to camel case string.
    // Since this is for tagnames only, no multibyte support.

    public static function convertTagNameToCamelcase(string $str, string $separator = '-', bool $uppercase_first = false): string
    {

        $str = implode(array_map('ucfirst', explode($separator, $str)));

        if (!$uppercase_first) {
            $str = lcfirst($str);
        }

        return $str;
    }


    // Converts an integer value between 0 and 255 to a character string.

    public static function ordToCharacter(int $ord): string|bool
    {

        return ($ord >= 0 && $order <= 255)
            ? pack("C*", $ord)
            : false;
    }


    /* Generator */

    // Creates a nonce string.

    public static function nonce(): string
    {

        return md5(microtime() . mt_rand());
    }


    // Generates a random number.

    public static function randomNumber(int $length = 32): int
    {

        return rand(pow(10, ($length - 1)), (pow(10, $length) - 1));
    }


    /* Formatter */

    // Strips special chars, translits, replaces whitespace with chosen separator.

    public static function tagname(
        string $str,
        ?int $limit = 100,
        string $separator = '-',
        // Special characters that should not be converted to the separator.
        array $chars_exclude = ["'", "\u{2019}"], // SINGLE QUOTE, RIGHT SINGLE QUOTATION MARK
        int $excess = 15,
        // Special characters that should be completely excluded from conversion to the separator.
        array $not_to_strip = ["'"]
    ): string {

        // Strips HTML and PHP tags from a string.
        $str = strip_tags($str);

        // Special characters that shouldn't be replaced with separator.
        if (!empty($chars_exclude)) {

            $str = str_replace($chars_exclude, '', $str);
        }

        // Strips all special chars.
        // \p{L} - unicode code in the category "letter"
        // \p{N} - any kind of numeric character
        $str = preg_replace('/[^\p{L}\p{N}' . preg_quote(implode($not_to_strip)) . ']+/u', ' ', $str);

        // Some special characters (like dot ".") will leave leading or trailing whitespace characters.
        $str = trim($str);

        // Translits.
        // http://php.net/manual/en/transliterator.transliterate.php
        // Failed to translit Sinhala (Sri Lanka) and Lao language. Example strings: "ලංකා", "ລາວ".
        #review: do I need Lower() when mb_strtolower is run below?
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $str);

        // Cleanup unwanted characters left behind by the transliterator (unknown reason why it leaves them). For example, "қаз".
        // Exclude whitespace in order to be able to count words.
        $str = preg_replace('/[^-\w\s]+/', '', $str);

        // Transform text to lowercase.
        $str = mb_strtolower($str, 'UTF-8');

        if (!is_null($limit)) {
            $str = self::mbLimit($str, $limit, '', $excess);
        }

        // Replace all whitespaces with the chosen separator.
        return preg_replace('/\s+|\s/', $separator, trim($str));
    }


    // Limits a string to a given number of characters by not cutting off the last word.

    public static function mbLimit(string $str, int $limit = 100, string $ellipsis = '...', int|bool $excess = 25): string
    {

        $strlen = mb_strlen($str);

        if ($strlen <= $limit) {
            return $str;
        }

        $use_ellipsis = true;

        // Split string into words (multibyte safe).
        $words = Str::mbSplitIntoWords($str, false); // Whether digits should be treated as part of words.

        $offset = 0;

        foreach ($words as $key => $word) {

            $pos = mb_strpos($str, $word, $offset);

            // Logically, this should never be "false", because the at this point the string is always longer than the limit.
            if ($pos !== false) {

                // Limit reached in between words, eg. right after word divider (whitespace, comma, etc.)
                if ($limit <= $pos) {

                    $pos = $limit;
                    break;
                }

                $word_length = mb_strlen($word);
                unset($words[$key]);

                // Ending of the word is at the limit point.
                if (($pos + $word_length) >= $limit) {

                    $pos += $word_length;
                    break;
                }

                $offset = $pos;
            }
        }

        // No trailing words left.
        if (empty($words)) {

            $pos = $strlen;
            $use_ellipsis = false;
        }

        // Manage excess.
        if (is_integer($excess)) {

            $critical_length = ($limit + $excess);

            if ($critical_length < $pos) {

                $pos = $critical_length;
                $use_ellipsis = true;
            }
        }

        $result = mb_substr($str, 0, $pos);

        if ($use_ellipsis && is_string($ellipsis)) {

            $result .= $ellipsis;
        }

        return $result;
    }


    // Gets number's ordinal suffix (eg. st, nd, rd, th).

    public static function getOrdinalSuffix(string|int $num): string
    {

        // Protect against large numbers.
        $num = ($num % 100);

        if ($num < 11 || $num > 13) {

            switch ($num % 10) {

                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
            }
        }

        return 'th';
    }


    // Splits a text into words and join all first letters of each word into a lowercase string.

    public static function mbAcronym(string $str): string
    {

        $words = Str::mbSplitIntoWords($str);
        $acronym = '';

        foreach ($words as $word) {

            if (!mb_strlen($word)) {
                continue;
            }

            $acronym .= mb_substr($word, 0, 1);
        }

        return mb_strtolower($acronym);
    }

    /**
     * Replaces variables in a template string with their corresponding values from an array
     *
     * @param string $template The template string containing variables to replace
     * @param array  $data     An associative array containing variable names as keys and their corresponding values
     * @return string Returns the modified template string on success, or false on failure
     */
    public static function parse(string $template, array $data): string
    {

        $regex = "/{(\w+[^}]*)}/";

        $replacements = preg_replace_callback($regex, function (array $matches) use ($data): string {

            // Check if the matched variable exists in the $data array
            if (isset($data[$matches[1]])) {
                return $data[$matches[1]];
            }

            // If not, return the original string
            return $matches[0];

        }, $template);

        return $replacements;
    }


    /**
     * Get the singular or plural form of a word based on the count
     *
     * @param int    $count    The count to determine whether to use singular or plural.
     * @param string $singular The singular form of the word.
     * @param string $plural   The plural form of the word.
     *
     * @return string The appropriate singular or plural form of the word.
     */
    public static function getSingularOrPlural(int $count, string $singular = '', string $plural = ''): string
    {

        return ($count === 1)
            ? $singular
            : $plural;
    }

    /**
     * Get the casual singular or plural form of a word based on a count.
     *
     * @param int    $count    The count to determine the form.
     * @param string $singular The singular form of the word.
     *
     * @return string The casual singular or plural form of the word based on the count.
     */
    public static function getCasualSingularOrPlural(int $count, string $singular): string
    {

        return self::getSingularOrPlural($count, $singular, ($singular . 's'));
    }
}
