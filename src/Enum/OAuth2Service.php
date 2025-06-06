<?php

namespace App\Enum;

enum OAuth2Service: string
{
    case GitHub = 'github';
    case Google = 'google';
}