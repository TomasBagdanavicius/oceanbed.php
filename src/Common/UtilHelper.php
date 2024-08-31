<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\String\Format;

class UtilHelper
{
    private mixed $last_result;


    public function __construct(

    ) {

    }


    //

    public function setLastResult(mixed $result): void
    {

        $this->last_result = $result;
    }


    //

    public function getLastResult(): mixed
    {

        return $this->last_result;
    }


    // Handles the "preg_match" function.

    public function regexPregMatch(string $pattern, string $subject, int $expected_elem_count = 1, int $flags = 0, int $offset = 0): self
    {

        $match = preg_match($pattern, $subject, $matches, $flags, $offset);

        if ($match == 0) {

            throw new \UnhandledMatchError(sprintf("Regex pattern \"%s\" does not match the given \"%s\" subject.", $pattern, $subject));

        } elseif (!$match) {

            throw new Exceptions\RegexMatchError(sprintf("There has been an error trying to match the given \"%s\" subject using regex pattern \"%s\".", $subject, $pattern));

        } elseif (empty($matches)) {

            throw Exceptions\EmptyElementException(sprintf("Regex pattern \"%s\" with subject \"%s\" did not yield any results.", $pattern, $subject));

        } elseif (($count_matches = count($matches)) != $expected_elem_count) {

            throw new \LengthException(sprintf("The number of matched elements (%d) is not equal to the expected number (%d).", $count_matches, $expected_elem_count));

        } else {

            foreach ($matches as $key => $match) {

                if (empty($match)) {

                    throw new Exceptions\EmptyElementException(sprintf("The %d%s element of the regex match came back empty using \"%s\".", ($key + 1), Format::getOrdinalSuffix(($key + 1)), $subject));
                }
            }
        }

        $this->setLastResult($matches);

        return $this;
    }


    //

    public function stringToTime(string $datetime, ?int $base_timestamp = null): self
    {

        if (!$timestamp = strtotime($datetime, $base_timestamp)) {

            throw new \LWP\Common\Exceptions\ConversionException(sprintf("Could not convert date string \"%s\" to timestamp.", $datetime));
        }

        $this->setLastResult($timestamp);

        return $this;
    }
}
