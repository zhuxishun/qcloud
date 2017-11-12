<?php

namespace Qcloud\Facades;

use Illuminate\Support\Facades\Facade;

class Auth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Qcloud\\Lib\\Auth';
    }
}
