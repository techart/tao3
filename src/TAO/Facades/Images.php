<?php

namespace TAO\Facades;

use Illuminate\Support\Facades\Facade;

class Images extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'taoImages';
    }
}