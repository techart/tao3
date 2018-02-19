<?php

define('LARAVEL_START', microtime(true));
$app_path = realpath(__DIR__.'/../../../../');

require $app_path.'/vendor/autoload.php';
require 'cfg.php';

$app = new \TAO\Application($app_path);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    TAO\App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    TAO\App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    TAO\App\Exceptions\Handler::class
);

$app->singleton('path.public', function() {
    $publicPath = config('app.public_path');
    return $publicPath ?: rtrim(base_path('www'), '/');
});

return $app;
