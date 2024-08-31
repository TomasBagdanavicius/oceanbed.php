<?php

declare(strict_types=1);

namespace LWP\Common\String;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\isDualIntegerIntersecting;

class Str
{
    /* Splitting */

    // Splits a string into lines.
    // - This performs better with short strings.

    public static function splitShortIntoLines(string $str): array
    {

        // - PHP_EOL represent end of line symbol for the current system only
        // - Only "\n" is not sufficient, because a line break is defined differently on different platforms; '\R' combines them all
        // - Used '$\R?^' previously, but it was leaving whitespace at the end
        // - 'u' flag added to enable utf-8 mode
        return preg_split('/\R/mu', $str);
    }


    // Splits a string into lines.
    // - This performs better with longer strings.

    public static function splitLongIntoLines(string $str): array
    {

        $lines = [];
        $separators = "\r\n";
        $line = strtok($str, $separators);

        while ($line !== false) {

            $lines[] = $line;
            $line = strtok($separators);
        }

        return $lines;
    }


    // Splits a string by whitespace elements. Ignores repeating whitespace characters.
    // - For splitting sentences into words consider using "str_word_count($str, 1)" instead.

    public static function splitIntoWords(string $str): array
    {

        return preg_split('/\s+/', $str);
    }


    // Splits multibyte string into words.

    public static function mbSplitIntoWords(string $str, bool $allow_digits = false): array
    {

        preg_match_all('/[\p{L}\p{M}' . ((!$allow_digits) ? '' : '\p{N}') . ']+/u', $str, $matches, PREG_PATTERN_ORDER);

        return $matches[0];
    }


    // Splits a string at the first occurence of a chosen divider character.

    public static function splitAtFirstDivider(string $str, string $divider): array
    {

        if ($divider == '') {
            return false;
        }

        $parts = explode($divider, $str, 2);

        return (count($parts) > 1)
            ? $parts
            : false;
    }


    // Splits a string by multiple strings.
    // This is a good alternative to "preg_split", especially when you don't want the regexp delimiter to match one of your dividers.

    public static function splitMultiple(string $str, array $dividers, \Closure $callback = null): array
    {

        $result = [];
        $has_callback = is_callable($callback);
        $current_pos = 0;

        do {

            $positions = self::posMultiple($str, $dividers);

            if ($has_dividers = !empty($positions)) {

                $next_divider_pos = min($positions);
                $current_pos += $next_divider_pos;
                $part = substr($str, 0, $next_divider_pos);
                $str = substr($str, ($next_divider_pos + 1));

            } else {

                $part = $str;
                $current_pos = -1;
            }

            $result[] = $part;

            if ($has_callback) {
                $callback($part, $current_pos);
            }

            if ($has_dividers) {
                $current_pos++;
            }

        } while ($has_dividers);

        return $result;
    }


    // Splits a string at provided multiple positions.

    public static function splitAtMultiplePos(string $str, array $positions): array
    {

        $result = [];
        $start = 0;
        $last_position = null;

        foreach ($positions as $position) {

            // Positions must be sorted numerically.
            if ($last_position !== null && $position < $last_position) {
                return $result;
            }

            $length = ($position - $start);
            $result[] = substr($str, $start, ($position - $start));
            $start += ($length + 1);
        }

        $result[] = substr($str, $start);

        return $result;
    }


    /* Trimming */

    // Strips word off the beginning and end of a string.
    // - This performs better with short strings.
    // - Since short strings are more common, this is the default function.
    // $ws_trim - whether to perform an additional whitespace trim.

    public static function trimSubstring(string $str, string $mask, bool $ws_trim = false): string
    {

        $ws_regex = (!$ws_trim)
            ? ''
            : '\s?';

        $quoted_mask = preg_quote($mask);

        return preg_replace('#^' . $quoted_mask . $ws_regex . '|' . $ws_regex . $quoted_mask . '$#', '', $str);
    }


    // Strips word, conditionally followed by a whitespace, from the beginning of a string.
    // $ws_trim - whether to perform an additional whitespace trim.

    public static function ltrimSubstring(string $str, string $mask, bool $ws_trim = false): string
    {

        $ws_regex = (!$ws_trim)
            ? ''
            : '\s{0,}';

        return preg_replace('#^' . preg_quote($mask) . $ws_regex . '#', '', $str);
    }


    // Strips word, conditionally preceeded by a whitespace, from the end of a string.
    // $ws_trim - whether to perform an additional whitespace trim.

    public static function rtrimSubstring(string $str, string $mask, bool $ws_trim = false): string
    {

        $ws_regex = (!$ws_trim)
            ? ''
            : '\s?';

        return preg_replace('#' . $ws_regex . preg_quote($mask) . '$#', '', $str);
    }


    // Strips word off the beginning and end of a string.
    // $ws_trim - whether to perform an additional whitespace trim.

    public static function trimSubstringLong(string $str, string $mask, bool $ws_trim = false): string
    {

        return self::rtrimSubstringLong(self::ltrimSubstringLong($str, $mask, $ws_trim), $mask, $ws_trim);
    }


    // Strips word (conditionally followed by a whitespace) off the beginning of a string.
    // $ws_trim - whether to perform an additional whitespace trim.

    public static function ltrimSubstringLong(string $str, string $mask, bool $ws_trim = false): string
    {

        if ($mask != '' && str_starts_with($str, $mask)) {
            $str = substr($str, strlen($mask));
        }

        return (!$ws_trim)
            ? $str
            : ltrim($str);
    }


    // Strips substring (conditionally preceeded by a whitespace) off the end of a string.
    // $ws_trim - whether to perform an additional whitespace trim.

    public static function rtrimSubstringLong(string $str, string $mask, bool $ws_trim = false): string
    {

        if ($mask != '' && str_ends_with($str, $mask)) {
            $str = substr($str, 0, -strlen($mask));
        }

        return (!$ws_trim)
            ? $str
            : rtrim($str);
    }


    // Multibyte safe trimming with options.
    // $chars - a set of characters that should be trimmed.
    // $side - 'both', 'leading', or 'trailing'.
    // $repeatable - whether any of the characters to be removed only once.

    public static function mbTrim(string $str, string $chars, string $side = 'both', bool $repeatable = false): string
    {

        $chars = mb_str_split($chars);
        $chars_index = [];

        foreach ($chars as $char) {

            $chars_index[mb_ord($char)] = [
                'lhs' => 0,
                'rhs' => 0,
            ];
        }

        do {

            $found = false;
            $lhs = mb_ord(mb_substr($str, 0, 1));

            if ($side != 'trailing' && array_key_exists($lhs, $chars_index) && ($repeatable || !$chars_index[$lhs]['lhs'])) {

                $found = true;
                $str = mb_substr($str, 1);
                $chars_index[$lhs]['lhs']++;
            }

            if (!mb_strlen($str)) {
                break;
            }

            $rhs = mb_ord(mb_substr($str, -1));

            if ($side != 'leading' && array_key_exists($rhs, $chars_index) && ($repeatable || !$chars_index[$rhs]['rhs'])) {

                $found = true;
                $str = mb_substr($str, 0, -1);
                $chars_index[$rhs]['rhs']++;
            }

        } while (mb_strlen($str) && $found);

        return $str;
    }


    // Strips off a substring off the beginning or end (available as option) of a given string.
    // $side - 'both', 'leading', or 'trailing'.
    // $repeatable - whether to look for another matching substring once the previous one has been stripped off.

    public static function mbTrimSubstring(string $str, string $mask, string $side = 'both', bool $repeatable = false): string
    {

        $mask_len = mb_strlen($mask);

        do {

            $found = false;

            if ($side != 'trailing' && mb_substr($str, 0, $mask_len) == $mask) {

                $found = true;
                $str = mb_substr($str, $mask_len);
            }

            if ($side != 'leading' && mb_substr($str, -$mask_len) == $mask) {

                $found = true;
                $str = mb_substr($str, 0, -$mask_len);
            }

        } while (mb_strlen($str) && $found && $repeatable);

        return $str;
    }


    // A multibyte safe function to trim off space characters off the beginning and end of a string.

    public static function mbTrimSpace(string $str): string
    {

        $i = 0;

        do {

            $char = mb_substr($str, $i, 1);

            $i++;

        } while (mb_ord($char) == 32);

        $i2 = 0;

        do {

            $i2++;

            $char = mb_substr($str, -$i2);

        } while (mb_ord($char) == 32);

        $start = ($i - 1);
        $length = -($i2 - 1);

        if ($start || $length) {

            $str = mb_substr($str, $start, ($length ?: null));
        }

        return $str;
    }


    /* Position */

    // Finds positions of all occurences of a substring in a string.

    public static function posAll(string $str, string $needle, int $offset = 0, bool $case_sensitive = true): array
    {

        $result = [];
        $needle_length = strlen($needle);
        $func_name = ($case_sensitive)
            ? 'strpos'
            : 'stripos';

        while (($pos = $func_name($str, $needle, $offset)) !== false) {

            $offset = ($pos + $needle_length);
            $result[] = $pos;
        }

        return $result;
    }


    // Finds the positions of the first occurrences of substrings in a string.
    // return (array) needle => position (zero based).

    public static function posMultiple(string $str, array $needles, int $offset = 0): array
    {

        $positions = [];

        foreach ($needles as $needle) {

            if (!array_key_exists($needle, $positions) && ($pos = strpos($str, $needle, $offset)) !== false) {
                $positions[$needle] = $pos;
            }
        }

        return $positions;
    }


    // Finds the closest needle in a string from a set of multiple needles.
    // Return (array) position, needle.

    public static function posMultipleClosest(string $str, array $needles, int $offset = 0): array
    {

        if (!$positions = self::posMultiple($str, $needles, $offset)) {
            return $positions;
        }

        $closest_pos = min($positions);

        return [
            $closest_pos,
            array_search($closest_pos, $positions),
        ];
    }


    // Finds the position of the first occurrence of a substring that is not preceeded by given substring.
    // $exc - substring that shouldn't preceded the needle.

    public static function posNotPrecededBy(string $str, string $needle, string $exc, int $offset = 0)
    {

        $exc_len = strlen($exc);

        do {

            $pos = strpos($str, $needle, $offset);
            $offset = ($pos + 1);

        } while ($pos && substr($str, max(0, ($pos - $exc_len)), min($pos, $exc_len)) == $exc);

        return $pos;
    }


    // Finds the positions of the first occurrences of substrings that are not preceeded by given substring.

    public static function posNotPrecededByMultiple(string $str, array $needles, string $exc, int $offset = 0): array
    {

        $positions = [];

        foreach ($needles as $needle) {

            if (($pos = self::posNotPrecededBy($str, $needle, $exc, $offset)) !== false) {
                $positions[$needle] = $pos;
            }
        }

        return $positions;
    }


    // Finds the closest needle in a string from a set of multiple needles that are not preceeded by given substring.

    public static function posNotPrecededByMultipleClosest(string $str, array $needles, string $exc, int $offset = 0)
    {

        if (!$positions = self::posNotPrecededByMultiple($str, $needles, $exc, $offset)) {
            return false;
        }

        $closest_pos = min($positions);

        return [
            $closest_pos,
            array_search($closest_pos, $positions),
        ];
    }


    //

    public static function accentInsensitivePosAll(string $str, string $needle, int $offset = 0, bool $case_sensitive = true): array
    {

        $transliterator = 'Any-Latin; Latin-ASCII';
        $str_latin = transliterator_transliterate($transliterator, $str);
        $needle_latin = transliterator_transliterate($transliterator, $needle);

        if (!$case_sensitive) {
            $str_latin = strtolower($str_latin);
            $needle_latin = strtolower($needle_latin);
        }

        return self::posAll($str_latin, $needle_latin, $offset);
    }


    /* Substr */

    // Captures everything until any of the needles is found.

    public static function substrUntilMultiple(string $str, array $needles, int $offset = 0): array
    {

        preg_match(
            '/.+?(?=(' . implode('|', array_map('preg_quote', $needles)) . '))/',
            $str,
            $result,
            PREG_OFFSET_CAPTURE,
            $offset
        );

        return (!empty($result))
            // collect string, needle, position
            ? [$result[0][0], $result[1][0], $result[1][1]]
            : $result;
    }


    /* Convertion */

    // Converts the whole string into ASCII characters map.
    // - ASCII table can be found at http://www.asciitable.com/.

    public static function toASCII(string $str, bool $include_char = false): array
    {

        $result = [];
        $str_length = strlen($str);

        for ($i = 0; $i < $str_length; $i++) {

            $char = $str[$i];
            $ord = ord($char);

            $result[] = (!$include_char)
                ? $ord
                : [$ord, $char,];
        }

        return $result;
    }


    // Dechunk a chunked string.
    // - This function supports LF line breaks, though in the RFC https://tools.ietf.org/html/rfc7230#section-4.1 the mentioned line break is CRLF.

    public static function dechunk(string $chunked_str): string
    {

        $result = '';
        $pos = 0;

        while ($pos < strlen($chunked_str)) {

            // Find the next LF or CR character.
            $closest_data = Str::posMultipleClosest(
                $chunked_str,
                ["\r","\n"],
                $pos
            );

            if (!$closest_data) {
                $closest_data[0] = strlen($chunked_str);
            }

            // Chunk length in hexadecimal number should be before line break.
            $chunk_len_hex = substr($chunked_str, $pos, ($closest_data[0] - $pos));

            // Is it a valid hexadecimal number?
            // If not, the sequence might be broken.
            if (!ctype_xdigit($chunk_len_hex)) {
                break;
            }

            // Move the pointer by heximal number plus a line break
            $pos += (strlen($chunk_len_hex) + 1);

            if ($pos >= strlen($chunked_str)) {
                break;
            }

            // Since, str[] is zero-based, I won't do "$pos+1".
            $next_pos_char_ord = ord($chunked_str[$pos]);

            // If it's a LF+CR or CR+LF line break, increment the pointer by one more point.
            if ($next_pos_char_ord == 10 || $next_pos_char_ord == 13) {
                $pos += 1;
            }

            // Hexadecimal to decimal.
            $chunk_len = hexdec($chunk_len_hex);

            // Chunk metadata says there is nothing below.
            if ($chunk_len == 0) {
                break;
            }

            // Get and prepend the chunk string.
            $chunk_part = substr($chunked_str, $pos, $chunk_len);
            $result .= $chunk_part;

            // Move the pointer forwards by chunk part string length plus a line break.
            $pos += (strlen($chunk_part) + 1);

            if (!isset($chunked_str[$pos])) {
                break;
            }

            // Since, str[] is zero-based, I won't do "$pos+1".
            $next_pos_char_ord = ord($chunked_str[$pos]);

            // If it's a LF+CR or CR+LF line break, increment the pointer by one more point.
            if ($next_pos_char_ord == 10 || $next_pos_char_ord == 13) {
                $pos += 1;
            }
        }

        return $result;
    }

    /**
     * Determines whether a given value can be converted to a string.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value can be converted to a string, false otherwise.
     */
    public static function canConvertToString(mixed $value): bool
    {

        $type = gettype($value);
        $accepted_types = [
            'boolean',
            'integer',
            'double',
            'string',
            'NULL',
        ];

        return (
            in_array($type, $accepted_types)
            || (is_object($value) && ($value instanceof \Stringable))
        );
    }


    /* Filtering */

    // Removes plain text line breaks.

    public static function removeLineBreaks(string $str): string
    {

        return preg_replace("/\r|\n/", '', $str);
    }


    /* Generator */

    // Generates a random string.
    // $charlist - ranges of characters to be included (note: "SP" stands for special characters).

    public static function random(int $length = 32, string $charlist = '0-9a-zA-ZSP'): string
    {

        $charlist = preg_replace_callback('#.-?.#', function (array $match): string {

            // "SP" stands for special characters.
            return ($match[0] != 'SP')
                ? implode('', range($match[0][0], $match[0][2]))
                : '!@#$%^&*()_';

        }, $charlist);

        $charlist_length = strlen($charlist);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $charlist[random_int(0, $charlist_length - 1)];
        }

        return $result;
    }


    // Calculates regular length based on the length of a base encoded data string.

    public static function calcLenFromBase64Len(int $len, int $padding): int
    {

        return intval((ceil($len / 4) * 3) - $padding);
    }


    // Calculates regular length from a base encoded bata string.

    public static function calcLenFromBase64Str(string $str): int
    {

        $result = -1;

        if (!empty($str)) {

            $padding = 0;

            if (str_ends_with($str, '==')) {
                $padding = 2;
            } elseif (str_ends_with($str, '=')) {
                $padding = 1;
            }

            $result = self::calcLenFromBase64Len(strlen($str), $padding);
        }

        return $result;
    }


    // Gives regular length for the length of a base encoded data string.

    public static function calcLenForBase64Len(int $len): int
    {

        $rounded_length = round((4 * $len / 3) + 3);
        return ($rounded_length & ~3);
    }


    // Gives regular length for a base encoded data string.

    public static function calcLenForBase64Str(string $str): int
    {

        return self::calcLenForBase64Len(strlen($str));
    }


    /* Replace */

    // In contrast to "str_replace" or "preg_replace" this function tries not to replace a previously inserted value by collecting an array of reserved/locked intervals where further insertions cannot take place.

    public static function replaceOnce(string|array $search, string|array $replace, string $subject, bool $return_reserved_intervals = false): string|array
    {

        $search = array_values((array)$search);
        $replace = array_values((array)$replace);
        $count_search = count($search);
        $count_replace = count($replace);

        if ($count_search !== $count_replace) {
            throw new \Error(sprintf(
                "The number of elements (%d) in the search array must match the number of elements (%d) in the replace array",
                $count_search,
                $count_replace
            ));
        }

        // A collection of arrays containing integer intervals where further replacements must not take place.
        $reserved_intervals = [];

        foreach ($search as $key => $search_term) {

            $positions = Str::posAll($subject, $search_term);

            // Search term(s) found.
            if ($positions) {

                $search_term_len = strlen($search_term);
                $offset = 0;

                foreach ($positions as $position) {

                    $intersecting = false;
                    // Position with added offset to correct for subject length changes.
                    $offset_position = ($position + $offset);

                    if ($reserved_intervals) {

                        $my_interval = [$offset_position, ($offset_position + $search_term_len - 1)];

                        foreach ($reserved_intervals as $interval) {

                            // Checks if this position intersects with any of the reserved intervals.
                            if (isDualIntegerIntersecting($my_interval, $interval)) {

                                $intersecting = true;
                                break;
                            }
                        }
                    }

                    if (!$intersecting) {

                        $replace_term = $replace[$key];
                        $replace_term_len = strlen($replace_term);
                        $term_len_diff = intval($search_term_len - $replace_term_len);

                        $subject = substr_replace($subject, $replace_term, $offset_position, $search_term_len);

                        // If search term length doesn't match replace term length, each succeeding interval should be amended, eg. when the subject is shortened, all intervals that go after the position where it was shortened must be decreased.
                        if ($term_len_diff) {

                            foreach ($reserved_intervals as &$reserved_interval) {

                                if ($offset_position < $reserved_interval[0]) {

                                    $reserved_interval[0] -= $term_len_diff;
                                    $reserved_interval[1] -= $term_len_diff;
                                }
                            }
                        }

                        $reserved_intervals[] = [$offset_position, ($offset_position + $replace_term_len - 1)];

                        // Offset the position as the subject length changes.
                        $offset -= $term_len_diff;
                    }
                }
            }
        }

        return (!$return_reserved_intervals)
            ? $subject
            : [$subject, $reserved_intervals];
    }


    /* Comparison */

    // Compares two strings

    public static function compare(string $value1, string $value2, bool $case_sensitive, bool $accent_sensitive): bool
    {

        if (!$accent_sensitive) {
            return self::accentInsensitiveCompare($value1, $value2, $case_sensitive);
        } elseif (!$case_sensitive) {
            return (strcasecmp($value1, $value2) === 0);
        } else {
            return ($value1 === $value2);
        }
    }


    // Accent-insensitive string comparison

    public static function accentInsensitiveCompare(string $string1, string $string2, bool $case_sensitive = true): bool
    {

        $transliterator = 'Any-Latin; Latin-ASCII';
        $string1_latin = transliterator_transliterate($transliterator, $string1);
        $string2_latin = transliterator_transliterate($transliterator, $string2);
        $function_name = ($case_sensitive)
            ? 'strcmp'
            : 'strcasecmp';

        return ($function_name($string1_latin, $string2_latin) === 0);
    }


    //

    public static function hasSameChars(string $string): bool
    {

        $chars = str_split($string);
        $unique_chars = array_unique($chars);

        return count($unique_chars) === 1;
    }


    // Adds given before and after strings around substrings at a given position in a string

    public static function stringWrap(string $str, array $positions, int $substring_len, string $before, string $after): string
    {

        $offset = 0;

        foreach ($positions as $position) {

            $index = ($position + $offset);
            $str = self::insertAt($str, $index, $before);
            $offset += strlen($before);

            $index = ($position + $offset + $substring_len);
            $str = self::insertAt($str, $index, $after);
            $offset += strlen($after);
        }

        return $str;
    }

    /**
     * Adds given before and after strings around substrings at a given position
     * in a multi-byte string
     *
     * @param string $str - Multi-byte string which will be modified
     * @param array $positions - Start index positions of the substrings
     * @param int $substring_len - Substring length
     * @param string $before - Before string that will be inserted at each start position
     * @param string $after - After string that will be inserted at each start position plus substring length
     * @return string Modified multi-byte string
     */
    public static function mbStringWrap(string $str, array $positions, int $substring_len, string $before, string $after): string
    {

        $offset = 0;

        foreach ($positions as $position) {

            $index = ($position + $offset);
            $str = self::mbInsertAt($str, $index, $before);
            $offset += mb_strlen($before);

            $index = ($position + $offset + $substring_len);
            $str = self::mbInsertAt($str, $index, $after);
            $offset += mb_strlen($after);
        }

        return $str;
    }

    /**
     * Inserts a string at a specified index in another string
     * @param string $str - Original string
     * @param int $index - Index at which the string will be inserted
     * @param string $insertion - String to be inserted
     * @return string
     */
    public static function insertAt(string $str, int $index, string $insertion): string
    {

        return (substr($str, 0, $index) . $insertion . substr($str, $index));
    }

    /**
     * Inserts a string at a specified index in another string
     * @param string $str - Original string
     * @param int $index - Index at which the string will be inserted
     * @param string $insertion - String to be inserted
     * @return string
     */
    public static function mbInsertAt(string $str, int $index, string $insertion): string
    {

        return (mb_substr($str, 0, $index) . $insertion . mb_substr($str, $index));
    }


    // Checks if given string contains ASCII characters only

    public static function isAscii(string $str): bool
    {

        return !preg_match('/[\\x80-\\xff]/', $str);
    }
}
