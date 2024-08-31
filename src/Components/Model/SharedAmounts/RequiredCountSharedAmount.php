<?php

declare(strict_types=1);

namespace LWP\Components\Model\SharedAmounts;

use LWP\Common\Exceptions\Math\IsNotUnsignedIntegerException;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;
use LWP\Components\Violations\RequiredObligationViolation;

class RequiredCountSharedAmount extends RangeCountSharedAmount
{
    /* Textual representations of the count numbers. */
    public const AT_LEAST_ONE = 0;

    /* Error Types */
    public const ERR_AT_LEAST_ONE = 1;
    public const ERR_EITHER_THIS_OR_OTHER = 2;
    public const ERR_EITHER_THIS_OR_OTHER_BUT_NOT_MORE_THAN = 3;
    public const ERR_EXCESSIVE_WHEN_SINGLE = 4;
    public const ERR_EXCESSIVE_WHEN_ABOVE_ONE = 5;
    public const ERR_EITHER_THIS_OR_OTHER_TO_REACH = 6;


    public function __construct(
        public readonly int $max_count = self::AT_LEAST_ONE,
        ?int $options = parent::THROW_ON_ACTION,
    ) {

        if ($max_count < 0) {
            throw new IsNotUnsignedIntegerException(
                "Integer for maximum value must not be below 0, got $max_count."
            );
        }

        $min_sum = $max_sum = null;

        if ($max_count === self::AT_LEAST_ONE) {
            $min_sum = 1;
        } else {
            $min_sum = $max_sum = $max_count;
        }

        parent::__construct($min_sum, $max_sum, $options);

        if ($this->state === parent::STATE_UNDERFLOW) {
            $this->setupLastViolation();
        }
    }


    //

    public function add(mixed $value = null, ?string $identifier = null): string|int
    {

        try {

            $result = parent::add(1, $identifier);

        } catch (SharedAmountOutOfBoundsException $exception) {

            throw $exception;

            // When option "THROW_ON_ACTION" is not set, exception will not be thrown, but the state might change.
        } finally {

            if ($this->state !== parent::STATE_VALID) {
                $this->setupLastViolation();
            }
        }

        return $result;
    }


    //

    public function remove(string|int $identifier): void
    {

        try {

            parent::remove($identifier);

        } catch (SharedAmountOutOfBoundsException $exception) {

            throw $exception;

            // When option "THROW_ON_ACTION" is not set, exception will not be thrown, but the state might change.
        } finally {

            if ($this->state !== parent::STATE_VALID) {
                $this->setupLastViolation();
            }
        }
    }


    //

    public function setupLastViolation(): RequiredObligationViolation
    {

        $current_value = $this->getValue();

        if ($current_value > $this->max_sum) {

            $violation = new RequiredObligationViolation(required_obligation: false, value: true);

            if ($this->max_sum === 1) {

                $message_str = "Excessive value: a single required value is already set.";
                $error_code = self::ERR_EXCESSIVE_WHEN_SINGLE;

            } else {

                $message_str = "Excessive value: {$this->max_sum} required values are already set.";
                $error_code = self::ERR_EXCESSIVE_WHEN_ABOVE_ONE;
            }

        } else {

            $violation = new RequiredObligationViolation(required_obligation: true, value: false);

            if ($this->max_sum > 1) {

                $message_str = "Either this or another value in group is required to reach {$this->max_sum}.";
                $error_code = self::ERR_EITHER_THIS_OR_OTHER_TO_REACH;

            } elseif ($current_value === 0) {

                if ($this->max_count === 0) {

                    $message_str = "At least one value in the group should be provided.";
                    $error_code = self::ERR_AT_LEAST_ONE;

                } elseif ($this->max_count === 1) {

                    $message_str = "Either this or another value in group should be provided.";
                    $error_code = self::ERR_EITHER_THIS_OR_OTHER;

                } else {

                    $message_str = "Either this or another value in group should be provided, but not more than $max_count.";
                    $error_code = self::ERR_EITHER_THIS_OR_OTHER_BUT_NOT_MORE_THAN;
                }
            }
        }

        $violation->setErrorMessageString($message_str);
        $violation->setErrorCode($error_code);

        $this->last_violation = $violation;

        return $violation;
    }
}
