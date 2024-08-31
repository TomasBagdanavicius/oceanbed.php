<?php

declare(strict_types=1);

namespace LWP\Components\Definitions\Enums;

enum DefinitionCategoryEnum: string
{
    case MAIN = 'main';
    case CONSTRAINT = 'constraint';
    case FORMATTING = 'formatting';
    case MODEL = 'model';
    case DATASET = 'dataset';
    case SHARED_AMOUNT = 'shared_amount';
    case MISC = 'misc';

}
