<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Common\OptionManager;
use LWP\Components\DataTypes\Custom\Number\Exceptions\UniversalNumberParserException;

class NumberPartParser
{
    public const NEGATIVE_SIGN_SYMBOL = '-';
    public const POSITIVE_SIGN_SYMBOL = '+';
    public const DEFAULT_INTEGER_PART_GROUP_SEPARATOR = ',';

    private ?string $separator = null;
    private array $groups = [];
    private int $group_count = 0;
    private int $first_group_length = 0;
    private ?int $generic_group_length = null;
    private bool $trailing_group_extended = false;
    private bool $is_signed = false;
    private int $leading_zeros_length = 0;
    private OptionManager $options;


    public function __construct(
        private string $number_part,
        array $options = []
    ) {

        $allowed_options = self::getAllowedOptionList();

        $this->options = new OptionManager(
            $options,
            $allowed_options,
            array_keys($allowed_options)
        );

        if ($number_part !== '') {
            $this->parse($number_part);
        }
    }


    //

    public function getNumberPart(): string
    {

        return $this->number_part;
    }


    //

    public static function getAllowedOptionList(): array
    {

        return [
            'group_separator' => null,
            /* Consider zerofill values as vanilla values, otherwise custom
            option would need to be provided to enable this. Thing is, this
            option is boolean type, meaning that there is no check in the code
            whether it should be applied or not. */
            'allow_leading_zeros' => true,
            'group_size' => false,
            'no_group_size_when_solid' => false,
            'allow_extended_trailing_group' => false,
        ];
    }


    //

    public function getGroupSeparator(): ?string
    {

        return $this->separator;
    }


    //

    public function getGroups(): array
    {

        return $this->groups;
    }


    //

    public function getGroupCount(): int
    {

        return $this->group_count;
    }


    //

    public function getFirstGroupLength(): int
    {

        return $this->first_group_length;
    }


    //

    public function containsSeparators(): bool
    {

        return ($this->group_count > 1);
    }


    //

    public function getGenericGroupLength(): ?int
    {

        return $this->generic_group_length;
    }


    //

    public function isSigned(): bool
    {

        return $this->is_signed;
    }


    //

    public function isTrailingGroupExtended(): bool
    {

        return $this->trailing_group_extended;
    }


    //

    public function getDigits(bool $preserve_leading_zeros = true): string
    {

        $digits = implode($this->groups);

        if (!$preserve_leading_zeros) {
            $digits = ltrim($digits, '0');
        }

        return $digits;
    }


    //

    public function getDigitsCount(): int
    {

        return strlen($this->getDigits());
    }


    //

    public function getIntegerDigitsCount(): int
    {

        return (strlen($this->getDigits(false)));
    }


    //

    public function getInteger(): int
    {

        $result = $this->getDigits($this->options->allow_leading_zeros);

        if ($this->is_signed) {
            $result = (self::NEGATIVE_SIGN_SYMBOL . $result);
        }

        return intval($result);
    }


    //

    public function getLeadingZerosLength(): int
    {

        return $this->leading_zeros_length;
    }


    //

    private function parse(string $number_part)
    {

        $number_part_full = $number_part;
        $is_signed = ($number_part[0] === self::NEGATIVE_SIGN_SYMBOL);

        // Has leading symbols.
        if ($is_signed || $number_part[0] === self::POSITIVE_SIGN_SYMBOL) {

            $number_part = substr($number_part, 1);

            if ($is_signed) {
                $this->is_signed = true;
            }
        }

        $number_part_len = strlen($number_part);
        // Character offset.
        $offset = 0;
        // Current group string.
        $curr_group = '';
        // Tracking leading zeros.
        $non_zero_found = false;

        while ($offset < $number_part_len) {

            $char = substr($number_part, $offset, 1);
            $is_last_char = (($number_part_len - $offset) === 1);

            // Current char is a digit.
            if ($is_digit = ctype_digit($char)) {

                if ($char === '0') {

                    if (!$offset && !$this->options->allow_leading_zeros) {
                        throw new UniversalNumberParserException(sprintf("Leading zeros are not allowed in number part \"%s\".", $number_part_full));
                    } elseif (!$non_zero_found && (!$offset || $prev_char === '0' || !ctype_digit($prev_char))) {
                        $this->leading_zeros_length++;
                    }

                } else {

                    $non_zero_found = true;
                }

                $curr_group .= $char;

                // Current char is not a digit.
            } else {

                if (!$offset) {
                    throw new UniversalNumberParserException(sprintf("Number part (%s) cannot start with a non-digit.", $number_part_full));
                } elseif (isset($prev_char) && !ctype_digit($prev_char)) {
                    throw new UniversalNumberParserException(sprintf("Number part (%s) must not contain succeeding non-digit character at position %d.", $number_part_full, ($offset + 1)));
                } elseif ($is_last_char) {
                    throw new UniversalNumberParserException(sprintf("Number part (%s) cannot end with a non-digit.", $number_part_full));
                }

                // Group separator hasn't been registered yet.
                if (!isset($group_separator)) {

                    // Validates against preferred group separator option.
                    if ($this->options->group_separator && $this->options->group_separator != $char) {
                        throw new \Exception(sprintf("Expected group separator \"%s\", found \"%s\" at position %d in number part \"%s\".", $this->options->group_separator, $char, ($offset + 1), $number_part_full));
                    }

                    $group_separator = $char;

                    // The same group separator must be used consistently.
                } elseif ($group_separator != $char) {

                    throw new UniversalNumberParserException(sprintf(
                        "Group separator mismatch at position %d in number part \"%s\"; expected \"%s\", found \"%s\"",
                        ($offset + 1),
                        $number_part_full,
                        $group_separator,
                        $char
                    ));
                }
            }

            if ((!$is_digit || $is_last_char)) {

                $this->group_count++;

                $curr_group_len = strlen($curr_group);

                // First group gathered.
                if ($this->group_count === 1) {

                    if (
                        // Group size option is available.
                        $this->options->group_size
                        // Group length is greater than the maximum preferred. First group is allowed to be smaller than the group size option, hence no "not equal to" comparison.
                        && $this->options->group_size < $curr_group_len
                        // When number part has no separators, check if it's allowed to pass through whole number that is longer than the preferred group size.
                        && ((!$this->options->no_group_size_when_solid && $is_last_char) || !$is_last_char)
                    ) {

                        throw new UniversalNumberParserException(sprintf("Length (%d) of the first group (%s) must not exceed the preferred group size of %d in number part \"%s\".", $curr_group_len, $curr_group, $this->options->group_size, $number_part_full));
                    }

                    $this->first_group_length = $this->generic_group_length = $curr_group_len;

                    // Non-first group gathered.
                } else {

                    $previous_group = $this->groups[array_key_last($this->groups)];
                    $previous_group_len = strlen($previous_group);

                    // Group size option is used and current group is longer than the option.
                    if (($group_size = $this->options->group_size) && $curr_group_len > $group_size) {

                        // Immediate error in the following cases:
                        if (
                            // Not the last group.
                            !$is_last_char
                            // Trailing extension is NOT allowed.
                            || !$this->options->allow_extended_trailing_group
                            // Otherwise, if trailing extension is allowed, it cannot exceed one point.
                            || $curr_group_len != ($group_size + 1)
                        ) {

                            throw new UniversalNumberParserException(sprintf("Length (%d) of group \"%s\" must match the preferred size of \"%d\" in number part \"%s\".", $curr_group_len, $curr_group, $group_size, $number_part_full));
                        }
                    }

                    // Second group.
                    if ($this->group_count == 2) {

                        $this->generic_group_length = $curr_group_len;

                        // First group longer than the second one.
                        if ($previous_group_len > $curr_group_len) {

                            throw new UniversalNumberParserException(sprintf("First group (%s) cannot be longer than the second group (%s) in number part \"%s\".", $previous_group, $curr_group, $number_part_full));
                        }

                        // Above the second group.
                    } else {

                        if ($previous_group_len != $curr_group_len) {

                            // Immediate error in the following cases:
                            if (
                                // Not the last group.
                                !$is_last_char
                                // Trailing extension is NOT allowed.
                                || !$this->options->allow_extended_trailing_group
                                // Otherwise, current group length must be bigger than previous by one point.
                                || ($curr_group_len - $previous_group_len) != 1
                            ) {

                                throw new UniversalNumberParserException(sprintf(
                                    "The length (%d) of group \"%s\" should match the length (%d) of group \"%s\" in number part \"%s\".",
                                    $curr_group_len,
                                    $curr_group,
                                    $previous_group_len,
                                    $previous_group,
                                    $number_part_full
                                ));

                            } elseif ($is_last_char) {

                                $this->trailing_group_extended = true;
                            }
                        }
                    }
                }

                $this->groups[] = $curr_group;

                if (isset($group_separator)) {
                    $this->separator = $group_separator;
                }

                $curr_group = '';
            }

            $prev_char = $char;
            $offset++;
        }
    }
}
