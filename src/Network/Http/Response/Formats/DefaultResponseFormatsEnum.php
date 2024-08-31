<?php

declare(strict_types=1);

namespace LWP\Network\Http\Response\Formats;

enum DefaultResponseFormatsEnum: string
{
    case HTML = 'html';
    case JSON = 'json';
    case SERIALIZE = 'serialize';
    case XML = 'xml';
    case HTMLJSON = 'htmljson';

}
