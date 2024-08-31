<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Common\Common;
use LWP\Network\Headers;
use LWP\Network\Message\Boundary as NetworkBoundary;
use LWP\Network\Message\PlainTextMessageBody;
use LWP\Network\Message\MultiPartMessage;

class Boundary extends NetworkBoundary
{
    public function __construct()
    {

        parent::__construct();
    }


    //

    public function add(mixed $part): ?int
    {

        // Accepts "BodyPart" objects only. Have to be compatible with the parent method.
        if (!($part instanceof BodyPart)) {
            Common::throwTypeError(1, __FUNCTION__, __NAMESPACE__ . '\BodyPart', gettype($part));
        }

        return parent::add($part);
    }


    //

    public function getContentTypeHeaderFieldValue(string $subtype = MultiPartMessage::SUBTYPE_FORM_DATA): string
    {

        return self::buildContentTypeHeaderFieldValue($this->delimiter, $subtype);
    }


    // Creates a new Boundary object from an array.

    public static function fromArray(array $array): self
    {

        $boundary = new self();

        foreach ($array as $key => $data) {

            if (is_string($data)) {

                $data = [
                    'name' => $key,
                    'contents' => $data,
                ];

            } elseif (is_array($data)) {

                $data['name'] ??= $key;

            } else {

                throw new \TypeError("Array values for parameter one must be of type string or array. Got \"" . gettype($data) . "\".");
            }

            if (!isset($data['contents']) && isset($data['filepath'])) {

                \LWP\Filesystem\Filesystem::exists($data['filepath']);

                $data['contents'] = file_get_contents($data['filepath']);
                $data['filename'] ??= basename($data['filepath']);
                $data['content_type'] = mime_content_type($data['filepath']);
            }

            if (!isset($data['contents'])) {

                throw new \Exception("Either contents or existing filepath must be provided.");
            }

            $header_data = [
                'content-length' => (string)strlen($data['contents']),
            ];

            if (isset($data['content_type'])) {
                $header_data['content-type'] = $data['content_type'];
            }

            if (!empty($data['headers']) && is_array($data['headers'])) {
                $header_data = array_merge($header_data, $data['headers']);
            }

            $body_part = new BodyPart(new PlainTextMessageBody($data['contents']), new Headers($header_data));

            $body_part->addDefaultContentDispositionHeaderField($data['name'], ($data['filename'] ?? null));

            $boundary->add($body_part);
        }

        return $boundary;
    }
}
