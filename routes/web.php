<?php

Route::get('/', function () {
	return view('main');
});

Route::get('/taoversion', function () {
	return view('version');
});

if (config('sitemap.dynamic')) {
	Route::get(config('sitemap.url'), '\\TAO\\Components\\Sitemap\\Controller@generate');
}

if (!\App::environment('testing')) {
	include base_path('routes/web.php');
}

TAO::routes();