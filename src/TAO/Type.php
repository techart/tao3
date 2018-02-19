<?php

namespace TAO;

class Type
{
    public static function isCallable($callback)
    {
        return Callback::isValidCallback($callback);
    }
}