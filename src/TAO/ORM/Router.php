<?php

namespace TAO\ORM;
use Illuminate\View\View;

class Router extends \TAO\Router
{
    public $item;

    public function routes()
    {
        foreach(\TAO::datatypeCodes() as $code) {
            $datatype = \TAO::datatype($code);
            $datatype->automaticRoutes();
        }
    }
}
