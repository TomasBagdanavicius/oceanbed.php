<?php

declare(strict_types=1);

namespace LWP\Components\Model\SharedAmounts;

use LWP\Common\Exceptions\Math\IsNotUnsignedIntegerException;
use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Constraints\MinSizeConstraint;
use LWP\Components\Constraints\SizeRangeConstraint;
use LWP\Components\Constraints\Validators\ConstraintValidator;
use LWP\Components\Constraints\Violations\MinSizeConstraintViolation;
use LWP\Components\Constraints\Violations\SizeRangeConstraintViolation;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountOutOfBoundsException;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountInvalidStateException;
use LWP\Components\Model\SharedAmounts\Exceptions\SharedAmountIdentifierNotFoundException;
use LWP\Components\Violations\Violation;

class RangeCountSharedAmount extends AbstractSharedAmount
{
    public const THROW_ON_ACTION = 1;
    public const THROW_WHEN_IN_PURSUE = 2;

    public const STATE_OPAQUE = 0;
    public const STATE_UNDERFLOW = 1;
    public const STATE_OVERFLOW = 2;
    public const STATE_VALID = 3;

    protected array $number_values = [];
    protected int $state = self::STATE_OPAQUE;
    private ConstraintValidator $constraint_validator;
    private bool $action_taken = false;
    protected ?Violation $last_violation = null;
    private bool $was_valid = false;


    public function __construct(
        public readonly null|int|float $min_sum = null,
        public readonly null|int|float $max_sum = null,
        public readonly ?int $options = self::THROW_ON_ACTION,
    ) {

        if ($min_sum === null && $max_sum === null) {
            throw new \InvalidArgumentException("Either \"min_sum\" or \"max_sum\" argument value must be provided.");
        }

        if ($min_sum < 0) {
            throw new IsNotUnsignedIntegerException("Minimum sum value must be above zero.");
        }

        if ($min_sum === null) {
            $constraint = new MaxSizeConstraint($max_sum);
        } elseif ($max_sum === null) {
            $constraint = new MinSizeConstraint($min_sum);
        } else {
            $constraint = new SizeRangeConstraint($min_sum, $max_sum);
        }

        $this->constraint_validator = $constraint->getValidator();
        $initial_validation_result = $this->constraint_validator->validate($this->getValue());

        if ($initial_validation_result instanceof Violation) {

            $this->setStateByViolation($initial_validation_result);
            $this->last_violation = $initial_validation_result;
        }
    }


    //

    public function __clone(): void
    {

        $this->constraint_validator = clone $this->constraint_validator;

        if ($this->last_violation) {
            $this->last_violation = clone $this->last_violation;
        }
    }


    //

    private function setStateByViolation(Violation $violation): void
    {

        $this->state = (
            ($violation instanceof MinSizeConstraintViolation)
            || (($violation instanceof SizeRangeConstraintViolation) && $violation->isUnderflow())
        ) ? self::STATE_UNDERFLOW : self::STATE_OVERFLOW;
    }


    //

    public function getState(): int
    {

        return $this->state;
    }


    //

    public function getValue(): int|float
    {

        return array_sum($this->number_values);
    }


    //

    public function getValues(): array
    {

        return $this->number_values;
    }


    //

    public function add(int|float $number, ?string $identifier = null): string|int
    {

        if ($identifier === null) {
            $this->number_values[] = $number;
            $identifier = array_key_last($this->number_values);
        } else {
            $this->number_values[$identifier] = $number;
        }

        $this->action_taken = true;

        $constraint_validator_result = $this->constraint_validator->validate($this->getValue());

        // No violation.
        if (!($constraint_validator_result instanceof Violation)) {

            $this->state = self::STATE_VALID;
            $this->was_valid = true;

            // Violation.
        } else {

            $violation = $constraint_validator_result;
            unset($constraint_validator_result);

            $this->setStateByViolation($violation);
            $this->last_violation = $violation;

            if (($this->options & self::THROW_ON_ACTION) && (($this->options & self::THROW_WHEN_IN_PURSUE) || !$this->isInPursue())) {
                $this->throwException();
            }
        }

        return $identifier;
    }


    //

    public function remove(string|int $identifier): void
    {

        if (!array_key_exists($identifier, $this->number_values)) {
            throw new SharedAmountIdentifierNotFoundException("Identifier \"$identifier\" was not found.");
        }

        unset($this->number_values[$identifier]);
        $this->action_taken = true;

        $constraint_validator_result = $this->constraint_validator->validate($this->getValue());

        // No violation.
        if (!($constraint_validator_result instanceof Violation)) {

            $this->state = self::STATE_VALID;

            // Violation.
        } else {

            $violation = $constraint_validator_result;
            unset($constraint_validator_result);

            $this->setStateByViolation($violation);
            $this->last_violation = $violation;

            if (($this->options & self::THROW_ON_ACTION) && (($this->options & self::THROW_WHEN_IN_PURSUE) || !$this->isInPursue())) {
                $this->throwException();
            }
        }
    }


    //

    public function isInInvalidState(): bool
    {

        return (!$this->action_taken && $this->state === self::STATE_UNDERFLOW);
    }


    //

    public function isInValidState(): bool
    {

        return !$this->isInInvalidState();
    }


    //

    public function isInExplicitValidState(): bool
    {

        return ($this->getState() === self::STATE_VALID);
    }


    //

    public function getException(): ?\Throwable
    {

        if ($this->isInInvalidState()) {

            return new SharedAmountInvalidStateException(
                "Shared amount is in invalid state.",
                previous: $this->last_violation->getExceptionObject(),
            );

        } elseif ($this->state !== self::STATE_VALID) {

            return new SharedAmountOutOfBoundsException(
                "Shared amount is out of bounds.",
                previous: $this->last_violation->getExceptionObject(),
            );

        } else {

            return null;
        }
    }


    //

    public function throwException(): mixed
    {

        if ($exception = $this->getException()) {
            throw $exception;
        } else {
            return null;
        }
    }


    //

    public function getLastViolation(): ?Violation
    {

        return $this->last_violation;
    }


    //

    public function isInPursue(): bool
    {

        return ($this->min_sum !== null && !$this->was_valid);
    }
}
