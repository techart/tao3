<?php
Route::get('/', '\\TAO\\Controller\\MainPage@index');
Route::get('/taoversion', '\\TAO\\Controller\\Utils@taoversion');

if (config('sitemap.dynamic')) {
	Route::get(config('sitemap.url'), '\\TAO\\Components\\Sitemap\\Controller@generate');
}

if (!\App::environment('testing')) {
	include base_path('routes/web.php');
}

TAO::routes();
