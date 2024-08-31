<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Common\Common;
use LWP\Filesystem\Filesystem;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Network\Uri\UriPathComponent;
use LWP\Network\Domain;
use LWP\Network\Http\Message\RequestHeaders;
use LWP\Network\Http\Message\StartLine;
use LWP\Network\Uri\Url;
use LWP\Network\Uri\SearchParams;
use LWP\Filesystem\Path\FilePath;

class Server
{
    // Gets host/domain name.
    // ~ Need to analyze "HTTP_X_FORWARDED_HOST" more.

    public static function getHost(bool $drop_www = false): string
    {

        return ($drop_www)
            // HTTP_HOST is the target host sent by the client. It can be manipulated freely by the user
            // SERVER_NAME can also be manipulated, though
            ? Domain::removeDefaultHostname($_SERVER['SERVER_NAME'])
            // SERVER_NAME is more reliable, but you're dependent on the server config!
            : $_SERVER['SERVER_NAME'];
    }


    // Determines if current address is "localhost".

    public static function isLocalhost(): bool
    {

        return (in_array($_SERVER['REMOTE_ADDR'], [
            '127.0.0.1',
            '::1'
        ]));
    }


    // Determines if current address is using "https" protocol.

    public static function isSecure(): bool
    {

        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == '443'
        );
    }


    // Gets protocol name (http or https).

    public static function getProtocolName(): string
    {

        return 'http'
            . (
                (self::isSecure())
                ? 's'
                : ''
            );
    }


    // Gets current address.

    public static function getAddress(): string
    {

        $protocol = self::getProtocolName();

        return $protocol
            . '://'
            . self::getHost()
            // Exclude default port.
            . (
                ($_SERVER['SERVER_PORT'] != Url::getDefaultPortNumberByScheme($protocol))
                ? ':' . $_SERVER['SERVER_PORT']
                : ''
            );
    }


    // Gets current working URL path.

    public static function getUrlPath(): string
    {

        return dirname($_SERVER['REQUEST_URI']);
    }


    // Checks if provided URL's host matches the internal host.

    public static function isInternalHost(string $url): bool
    {

        $host = parse_url($url, PHP_URL_HOST);

        return (
            !empty($host)
            && (strcasecmp($host, self::getHost()) === 0)
        );
    }


    // Converts file pathname to URL string

    public static function getUrlFromPathname(string $pathname): false|string
    {

        $path = PathEnvironmentRouter::getStaticInstance();
        $pathname = (!$path::isAbsolute($pathname))
            ? self::join($_SERVER['DOCUMENT_ROOT'], $pathname)
            : $path::normalize($pathname);
        $document_root = $path::normalize($_SERVER['DOCUMENT_ROOT']);

        if (!str_starts_with($pathname, $document_root)) {
            return false;
        }

        return (
            self::getAddress()
            . str_replace(
                $path::SEPARATORS,
                UriPathComponent::SEPARATOR,
                substr($pathname, strlen($document_root))
            )
        );
    }


    // Converts file path object to URL

    public static function getUrlFromFilePath(FilePath $file_path): false|Url
    {

        $url_str = self::getUrlFromPathname($file_path->__toString());

        return ($url_str)
            ? new Url($url_str)
            : false;
    }


    // Gets current address containing path (query is excluded).

    public static function getCurrentDirname(): string
    {

        return (self::getAddress() . self::getUrlPath());
    }


    // Outputs a file.

    public static function outputFile(string $filename, string $output_name = null): never
    {

        Filesystem::exists($filename);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . ($output_name ?? basename($filename)) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);

        exit;
    }


    // Gets current active URL string.

    public static function getCurrentUrlString(): string
    {

        return (self::getAddress() . $_SERVER['REQUEST_URI']);
    }


    // Gets current active URL instance object.

    public static function getCurrentUrl(): Url
    {

        $current_url = new Url(self::getAddress());
        $path_component = new UriPathComponent($_SERVER['SCRIPT_NAME']);
        $current_url->setPathComponent($path_component);

        if (!empty($_SERVER['QUERY_STRING'])) {

            $search_params = SearchParams::fromString($_SERVER['QUERY_STRING']);
            $current_url->setQueryComponent($search_params);
        }

        return $current_url;
    }


    // Returns the start line

    public static function getStartLine(): StartLine
    {

        $protocol_version = substr($_SERVER['SERVER_PROTOCOL'], 5);
        $http_method_enum_case = Common::findEnumCase(HttpMethodEnum::class, strtoupper($_SERVER['REQUEST_METHOD']));

        return new StartLine($http_method_enum_case, $_SERVER['REQUEST_URI'], $protocol_version);
    }


    //

    public static function getRequestHeadersArray(): array
    {

        // With apache, use the default way to get headers
        if (function_exists('apache_request_headers')) {

            $request_headers = apache_request_headers();

            if ($request_headers) {
                return array_change_key_case($request_headers, CASE_LOWER);
            }
        }

        $headers = [];

        foreach ($_SERVER as $name => $value) {

            if (substr($name, 0, 5) === 'HTTP_') {

                $header_field_name = strtolower(str_replace('_', '-', substr($name, 5)));
                $headers[$header_field_name] = $value;
            }
        }

        return $headers;
    }


    // Returns cacheable request headers object

    public static function getRequestHeaders(): RequestHeaders
    {

        static $headers;

        if (!$headers) {

            $headers = new RequestHeaders(self::getStartLine());
            $request_headers_array = self::getRequestHeadersArray();

            foreach ($request_headers_array as $name => $value) {
                $headers->set($name, $value);
            }
        }

        return $headers;
    }
}
