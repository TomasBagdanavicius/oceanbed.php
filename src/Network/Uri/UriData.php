<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Network\Uri\UriReference;
use LWP\Network\Uri\Exceptions\InvalidUriException;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\splitToKey;
use function LWP\Common\Array\Arrays\keyPrependToValue;

class UriData extends UriReference
{
    public const SCHEME = 'data';
    public const SEPARATOR = ',';


    public function __construct(string $uri)
    {

        if ($uri === '') {
            throw new \Exception("URI cannot be empty.");
        }

        parent::__construct($uri);

        $scheme = $this->getScheme();

        if ($scheme !== '' && $scheme !== self::SCHEME) {
            throw new InvalidUriException(sprintf("URI \"%s\" must start with a \"%s\" scheme.", $uri, self::SCHEME));
        }

        $this->splitPathQueryFragment();
    }


    // Adds custom query handling behavior when parsing path, query, and fragment parts.

    public function splitPathQueryFragment(): void
    {

        parent::splitPathQueryFragment();

        if (!($this->parts['query'] instanceof SearchParams)) {

            $this->setQueryString($this->parts['query']);
        }

        if (!is_array($this->parts['path'])) {

            $path_string = $this->parts['path']->__toString();

            $result = [
                'media_type' => '',
                'media_type_params' => [],
                'is_base64' => '',
                'data' => '',
            ];

            // Is it safe to split by comma, or can there be preceeding quoted commas?
            $main_parts = explode(self::SEPARATOR, $path_string, 2);

            $params = explode(';', $main_parts[0]);
            $params_count = count($params);

            // Data was encoded in base64.
            if ($params[($params_count - 1)] == 'base64') {
                $result['is_base64'] = 'base64';
                array_pop($params);
            }

            // Assuming that the first param is the media type.
            if (!empty($params)) {
                $result['media_type'] = array_shift($params);
            }

            $result['media_type_params'] = splitToKey($params, '=');

            if (isset($main_parts[1])) {
                $result['data'] = $main_parts[1];
            }

            $this->parts['path'] = $result;
        }
    }


    // Gets parts for the whole URI or a portion of it.

    public function getUriReferenceParts(string $from = null, string $until = null): array
    {

        $parts = parent::getUriReferenceParts($from, $until);

        if (isset($parts['query']) && $parts['query'] instanceof SearchParams) {
            $parts['query'] = $parts['query']->outputWithPrefix();
        }

        if (isset($parts['path']) && is_array($parts['path'])) {

            $result = [];

            if ($parts['path']['media_type'] != '') {
                $result[] = $parts['path']['media_type'];
            }

            if (!empty($parts['path']['media_type_params'])) {
                $result = array_merge($result, keyPrependToValue($parts['path']['media_type_params'], '='));
            }

            if ($parts['path']['is_base64'] != '') {
                $result[] = $parts['path']['is_base64'];
            }

            $media_type = implode(';', $result);

            $parts['path'] = ($media_type . self::SEPARATOR . $parts['path']['data']);
        }

        return $parts;
    }


    // Sets a given query string.

    public function setQueryString(string $query_string): void
    {

        $this->parts['query'] = SearchParams::fromString($query_string);
    }


    // Gets the media type.

    public function getMediaType(): string
    {

        return $this->parts['path']['media_type'];
    }


    // Sets the media type.

    public function setMediaType(string $media_type): void
    {

        $this->parts['path']['media_type'] = $media_type;
    }


    // Gets media type params.

    public function getMediaTypeParams(): array
    {

        return $this->parts['path']['media_type_params'];
    }


    // Sets media type param.

    public function setMediaTypeParam(string $name, string $value): void
    {

        $this->parts['path']['media_type_params'][$name] = $value;
    }


    // Unsets media type param.

    public function unsetMediaTypeParam(string $name): void
    {

        if (isset($this->parts['path']['media_type_params'][$name])) {

            unset($this->parts['path']['media_type_params'][$name]);
        }
    }


    // Tells if data has been base64 encoded.

    public function isBase64(): bool
    {

        return (!empty($this->parts['path']['is_base64']));
    }


    // Enables base 64 encode.

    public function enableBase64(): void
    {

        $this->parts['path']['is_base64'] = 'base64';
    }


    // Disables base 64 encode.

    public function disableBase64(): void
    {

        $this->parts['path']['is_base64'] = '';
    }


    // Gets decoded data.

    public function getData(): string
    {

        return ($this->isBase64())
            ? base64_decode($this->parts['path']['data'])
            : rawurldecode($this->parts['path']['data']);
    }


    // Gets raw undecoded data.

    public function getRawData(): string
    {

        return $this->parts['path']['data'];
    }


    // Sets data.

    public function setData(string $data, bool $is_base64_encoded = true): void
    {

        if ($is_base64_encoded) {
            $this->enableBase64();
        } else {
            $this->disableBase64();
        }

        $this->parts['path']['data'] = $data;
    }
}
