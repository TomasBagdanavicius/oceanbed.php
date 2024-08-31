<?php

declare(strict_types=1);

namespace LWP\Network\Http\Enums;

enum ResponseStatusCodesEnum: int
{
    // 100 - 199 Informational
    case CONTINUE = 100;
    // 200 - 299 Successful
    case OK = 200;
    // 300 - 399 Redirection
    case MULTIPLE_CHOICES = 300;
    // 400 - 499 Client error
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    // 500 - 599 Server error
    case INTERNAL_SERVER_ERROR = 500;

}
