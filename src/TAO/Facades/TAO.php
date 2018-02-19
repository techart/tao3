<?php

namespace TAO\Facades;

use Illuminate\Support\Facades\Facade;

class TAO extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tao';
    }
}