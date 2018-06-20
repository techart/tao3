<?php

if (empty($appPath)) {
	$appPath = realpath(__DIR__ . '/../../../../');
}
if (empty($vendorPath)) {
	$vendorPath = $appPath . '/vendor';
}

require $vendorPath . '/autoload.php';
require 'helpers.php';
require 'cfg.php';

$app = new \TAO\Application($appPath);

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
