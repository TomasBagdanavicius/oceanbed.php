<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Network\Http\RequestMessage;
use LWP\Network\Uri\UriReference;
use LWP\Network\Http\Message\Exceptions\InvalidStartLineException;
use LWP\Network\Uri\UriPathComponent;
use LWP\Network\Http\HttpMethodEnum;

class StartLine implements \Stringable
{
    public function __construct(
        public readonly HttpMethodEnum $method,
        private string $request_target,
        private string $protocol_version
    ) {

    }


    // Outputs the start line as a string.

    public function __toString(): string
    {

        return self::buildStartLine($this->method, $this->request_target, $this->protocol_version);
    }


    // Outputs the start line components as an array.

    public function toArray(): array
    {

        return [
            'method' => $this->method,
            'request_target' => $this->request_target,
            'protocol_version' => $this->protocol_version,
        ];
    }


    // Gets the request target.

    public function getRequestTarget(): string
    {

        return $this->request_target;
    }


    // Gets the protocol version.

    public function getProtocolVersion(): string
    {

        return $this->protocol_version;
    }


    // Creates new object instance from a string.

    public static function fromString(string $start_line_str): self
    {

        $data = self::parseStartLine($start_line_str);

        return new self($data['method'], $data['request_target'], $data['protocol_version']);
    }


    // Parses a start line string into its components.

    public static function parseStartLine(string $start_line_str): array
    {

        $start_line_str = trim($start_line_str);
        $leading_parts = explode(' ', $start_line_str, 2);

        RequestMessage::validateMethod($leading_parts[0], throw: true);

        if (!isset($leading_parts[1])) {
            throw new InvalidStartLineException("Start line is not full and contains HTTP method only.");
        }

        $next_part = trim($leading_parts[1]);
        $next_part_components = explode(' ', $next_part);

        if (empty($next_part_components[0])) {
            throw new InvalidStartLineException("Start line is missing a resource target.");
        }

        if (!isset($next_part_components[1])) {
            throw new InvalidStartLineException("Protocol part is missing.");
        }

        $protocol_part = explode('/', $next_part_components[1]);

        if (strcasecmp($protocol_part[0], 'http') !== 0) {
            throw new InvalidStartLineException("Status line must start with a \"HTTP\" string.");
        }

        if (!isset($protocol_part[1]) || !is_numeric($protocol_part[1])) {
            throw new InvalidStartLineException("HTTP string must be followed by numeric protocol version.");
        }

        $http_methods = $methods = HttpMethodEnum::cases();

        foreach ($http_methods as $http_method_enum) {

            if ($http_method_enum->name === $leading_parts[0]) {
                $leading_parts[0] = $http_method_enum;
                break;
            }
        }

        return [
            'method' => $leading_parts[0],
            'request_target' => $next_part_components[0],
            'protocol_version' => $protocol_part[1],
        ];
    }


    // Builds start line string.

    public static function buildStartLine(HttpMethodEnum $method, string $request_target, string $http_protocol_version): string
    {

        return ($method->name . ' ' . $request_target . ' HTTP/' . $http_protocol_version);
    }


    // Builds request target string.

    public static function buildRequestTarget(HttpMethodEnum $method, UriReference $uri, bool $is_proxy = false): string
    {

        if ($method == HttpMethodEnum::CONNECT) {

            // "authority-form"
            return $uri->getUriReference('host', 'port');
        }

        if ($is_proxy) {

            // "absolute-form"
            return $uri->getUriReference('scheme', 'query');
        }

        // With URL, an empty path will result in '/', but it should be fine.
        $uri_path_query = $uri->getUriReference('path', 'query');

        if ($method == HttpMethodEnum::OPTIONS && empty($uri_path_query)) {

            // "asterisk-form" - it refers to the entire server.
            return '*';
        }

        return (!empty($uri_path_query))
            ? $uri_path_query
            : UriPathComponent::SEPARATOR;
    }
}
