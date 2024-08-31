<?php

declare(strict_types=1);

use LWP\Network\Http\ResponseBuffer;

$response_state = $response->getState();

if ($response_state == ResponseBuffer::STATE_COMPLETED) {

    echo "Completed.", PHP_EOL;
    print_r($response->getBody());

} elseif ($response_state == ResponseBuffer::STATE_ABORTED) {

    echo "Aborted.", PHP_EOL;

    $messages = $response->getMessages();

    foreach ($messages as $message) {
        echo $message->text, PHP_EOL;
    }
}
