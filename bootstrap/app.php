<?php

/**
  * @var string $appPath
  * @var string $vendorPath
**/


if (empty($appPath)) {
	$appPath = realpath(__DIR__ . '/../../../../');
}

if (empty($vendorPath)) {
	$vendorPath = $appPath . '/vendor';
}

require $vendorPath . '/autoload.php';
require 'helpers.php';
require 'cfg.php';
require 'lang.php';

/**
  * @var string $appClass
**/
$appClass = $appClass ?? \TAO\Application::class;


/**
  * @var \Illuminate\Foundation\Application $app
**/
$app = new $appClass($appPath);

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
	/**
	  * @var string $publicPath
	**/
    $publicPath = config('app.public_path');
    return $publicPath ?: rtrim(base_path('www'), '/');
});

return $app;
