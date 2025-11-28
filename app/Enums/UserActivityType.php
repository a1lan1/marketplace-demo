<?php

declare(strict_types=1);

namespace App\Enums;

enum UserActivityType: string
{
    case PAGE_VIEW = 'page_view';
    case CLICK = 'click';
    case SIGN_IN = 'sign_in';
    case SIGN_UP = 'sign_up';
    case ERROR = 'error';
}
