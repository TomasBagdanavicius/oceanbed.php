<?php

declare(strict_types=1);

namespace LWP\Common\Enums;

enum CrudOperationEnum: string
{
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
}
