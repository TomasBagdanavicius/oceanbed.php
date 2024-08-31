<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Common\Common;

class StreamedMessageBody
{
    private $stream;


    public function __construct($stream)
    {

        if (is_stream($stream)) {
            Common::throwTypeError(1, __FUNCTION__, 'resource', gettype($stream));
        }

        $this->stream = $stream;
    }


    //

    public function getSize(): int
    {

        $stat = fstat($this->stream);

        return $stat['size'];
    }
}
