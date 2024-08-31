<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

use LWP\Components\Messages\Message;
use LWP\Components\Messages\MessageBuilder;

abstract class Violation
{
    protected ?string $error_message_str = null;
    protected ?string $extended_error_message_str = null;
    protected ?int $error_code = null;


    public function __construct(
        protected mixed $constraint_value,
        protected mixed $value,
    ) {

    }


    // Gets error message string format (usually to be used with "sprintf").

    abstract public function getErrorMessageFormat(): string;


    // Gets the constraint rule value.

    public function getConstraintValue(): mixed
    {

        return $this->constraint_value;
    }


    // Gets the provided value which does not satisfy the constraint value.

    public function getValue(): mixed
    {

        return $this->value;
    }


    // Gets extended error message string format (usually to be used with "sprintf").

    public function getExtendedErrorMessageFormat(): string
    {

        return $this->getErrorMessageFormat();
    }


    // Sets custom error message string.

    public function setErrorMessageString(string $message_str): void
    {

        $this->error_message_str = $message_str;
    }


    // Gets formatted error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->value));
    }


    // Sets custom extended error message string.

    public function setExtendedErrorMessageString(string $message_str): void
    {

        $this->extended_error_message_str = $message_str;
    }


    // Gets formatted extended error message string.

    public function getExtendedErrorMessageString(): string
    {

        return ($this->extended_error_message_str
            ?: $this->getErrorMessageString());
    }


    // Sets the error code integer.

    public function setErrorCode(int $error_code): void
    {

        $this->error_code = $error_code;
    }


    // Gets the error code integer.

    public function getErrorCode(): ?int
    {

        return $this->error_code;
    }


    // Gets the exception object.

    public function getExceptionObject(?string $exception_class_name = '\Exception'): \Throwable
    {

        $params = [
            'message' => $this->getErrorMessageString(),
        ];

        if ($this->error_code !== null) {
            $params['code'] = $this->error_code;
        }

        return new ($exception_class_name)(...$params);
    }


    // Throws the error message using the provided exception class.

    public function throwException(?string $exception_class_name = '\Exception'): void
    {

        throw $this->getExceptionObject($exception_class_name);
    }


    // Gets the error message as a message object.

    public function getErrorMessage(): Message
    {

        $message = (new MessageBuilder(Message::MESSAGE_ERROR, $this->getErrorMessageString()))
            ->getMessageInstance();

        if ($this->error_code !== null) {
            $message->setCode($this->error_code);
        }

        return $message;
    }


    // Offers possible violation correction opportunities or options.

    public function getCorrectionOpportunities(): ?array
    {

        return null;
    }
}
