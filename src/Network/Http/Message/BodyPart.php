<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Network\Message\BodyPart as NetworkBodyPart;
use LWP\Network\Headers;
use LWP\Network\Message\MessageBody;

class BodyPart extends NetworkBodyPart
{
    public function __construct(
        MessageBody $body,
        ?Headers $headers = null,
    ) {

        parent::__construct($body, $headers);
    }


    // Sets the "content-disposition" field.

    public function addDefaultContentDispositionHeaderField(string $name, ?string $filename = null): void
    {

        $this->headers->set('content-disposition', self::buildContentDispositionHeaderFieldValue($name, $filename));
    }


    // Builds "content-disposition" field's value.

    public static function buildContentDispositionHeaderFieldValue(string $name, ?string $filename = null): string
    {

        $result = ('form-data; name="' . $name . '"');

        if ($filename) {
            $result .= (' filename="' . addcslashes(basename($filename), '"\\') . '"');
        }

        return $result;
    }
}
