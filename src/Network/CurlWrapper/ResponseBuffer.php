<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Network\Http\ResponseBuffer as HttpResponseBuffer;
use LWP\Network\HeaderCollection;

class ResponseBuffer extends HttpResponseBuffer
{
    private HeaderCollection $header_collection;
    private array $status_lines = [];


    public function __construct(Request $request)
    {

        parent::__construct($request);

        $this->header_collection = new HeaderCollection();
    }


    // Gets the headers collection object.

    public function getHeaderCollection(): HeaderCollection
    {

        return $this->header_collection;
    }


    // Archives the current headers container by adding it to the archives collection.

    public function closeHeaders(): void
    {

        $this->header_collection->add($this->response_headers);
    }


    // Sets transfer info from array.

    public function setTransferInfoFromArray(array $data): TransferInfo
    {

        $transfer_info = new TransferInfo($data);

        $this->setTransferInfo($transfer_info);

        return $transfer_info;
    }
}
