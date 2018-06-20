<?php

return [
	'dynamic' => true,
	'url' => '/sitemap.xml',
	'cache' => [
		'lifetime' => 60
	],
	'limit' => 50000,
	'sources' => [
		\TAO\Components\Sitemap\MainPageSource::class
	],
	'main_page' => [
		'url' => '/',
		'changefreq' => 'daily',
		'priority' => 1,
	]
];
