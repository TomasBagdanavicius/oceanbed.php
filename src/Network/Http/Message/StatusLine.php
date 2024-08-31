<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Network\Http\Message\Exceptions\InvalidStatusLineException;

/* The Status-Line, consisting of the protocol version followed by a numeric status code and its associated textual phrase, with each element separated by SP characters. No CR or LF is allowed except in the final CRLF sequence. */
class StatusLine implements \Stringable
{
    private string $protocol_version;
    private int $status_code;
    private ?string $reason_phrase = null;


    public function __construct(string $protocol_version, int $status_code, ?string $reason_phrase = null)
    {

        $this->protocol_version = $protocol_version;
        $this->status_code = $status_code;
        $this->reason_phrase = $reason_phrase;
    }


    // Outputs the status line as a string.

    public function __toString(): string
    {

        return self::buildStatusLine($this->protocol_version, $this->status_code, $this->reason_phrase);
    }


    // Outputs the status line components as an array.

    public function toArray(): array
    {

        return [
            'protocol_version' => $this->getProtocolVersion(),
            'status_code' => $this->getStatusCode(),
            'reason_phrase' => $this->getReasonPhrase(),
        ];
    }


    // Gets the protocol version.

    public function getProtocolVersion(): string
    {

        return $this->protocol_version;
    }


    // Gets the status code.

    public function getStatusCode(): int
    {

        return $this->status_code;
    }


    // Gets the reason phrase.

    public function getReasonPhrase(): ?string
    {

        return $this->reason_phrase;
    }


    // Creates new object instance from a string.

    public static function fromString(string $status_line_str): self
    {

        $data = self::parseStatusLine($status_line_str);

        return new self($data['protocol_version'], $data['status_code'], $data['reason_phrase']);
    }


    // Parses and validates a status line string.

    public static function parseStatusLine(string $status_line_str): array
    {

        $status_line_str = trim($status_line_str);
        $leading_parts = explode(' ', $status_line_str, 2);

        $http_version_parts = explode('/', $leading_parts[0]);

        if (count($http_version_parts) != 2) {
            throw new InvalidStatusLineException("Incorrect HTTP version string.");
        }

        if (strcasecmp($http_version_parts[0], 'http') !== 0) {
            throw new InvalidStatusLineException("Status line must start with a \"HTTP\" string.");
        }

        if (!is_numeric($http_version_parts[1])) {
            throw new InvalidStatusLineException("HTTP string must be followed by numeric protocol version.");
        }

        if (!isset($leading_parts[1])) {
            throw new InvalidStatusLineExceptionn("Status code is missing.");
        }

        $status_part = trim($leading_parts[1]);
        $status_part_components = explode(' ', $status_part, 2);

        if (!is_numeric($status_part_components[0]) || strlen($status_part_components[0]) != 3) {
            throw new InvalidStatusLineException("Invalid status code");
        }

        return [
            'protocol_name' => $http_version_parts[0],
            'protocol_version' => $http_version_parts[1],
            'status_code' => intval($status_part_components[0]),
            'reason_phrase' => (isset($status_part_components[1]))
                ? $status_part_components[1]
                : null,
        ];
    }


    // Builds status line string.

    public static function buildStatusLine(string $protocol_version, int $status_code, string $reason_phrase = null): string
    {

        $result = ('HTTP/' . $protocol_version . ' ' . $status_code);

        if ($reason_phrase) {
            $result .= (' ' . $reason_phrase);
        }

        return $result;
    }
}
