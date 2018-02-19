<?php

namespace TAO\Frontend;

use Techart\Frontend\Environment;

class Env extends Environment
{
    public static function instanse()
    {
        static $instanse = null;
        if (!$instanse) {
            $storage = new EnvStorage();
            $instanse = new self($storage);
        }
        return $instanse;
    }
}