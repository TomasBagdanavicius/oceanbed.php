<?php

declare(strict_types=1);

namespace LWP\Network\Http;

enum HttpMethodEnum
{
    case HEAD; // Same as GET but returns only HTTP headers and no document body.
    case GET; // Requests data from a specified resource.
    case POST; // Submits data to be processed to a specified resource.
    case PUT; // For putting or updating a resource on the server.
    case DELETE; // For deleting the request resource.
    case OPTIONS; // Returns the HTTP methods that the server supports.
    case TRACE; // Performs a message loop-back test along the path to the target resource, providing a useful debugging mechanism.
    case CONNECT; // Starts two-way communications with the requested resource.
    case PATCH; // Applies partial modifications to a resource.

}
