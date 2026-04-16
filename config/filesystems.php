<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default filesystem disk that should be used
	| by the framework. The "local" disk, as well as a variety of cloud
	| based disks are available to your application. Just store away!
	|
	*/

	'default' => 'local',

	/*
	|--------------------------------------------------------------------------
	| Default Cloud Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Many applications store files both locally and in the cloud. For this
	| reason, you may specify a default "cloud" driver here. This driver
	| will be bound as the Cloud disk implementation in the container.
	|
	*/

	'cloud' => 's3',

	/*
	|--------------------------------------------------------------------------
	| Filesystem Disks
	|--------------------------------------------------------------------------
	|
	| Here you may configure as many filesystem "disks" as you wish, and you
	| may even configure multiple disks of the same driver. Defaults have
	| been setup for each driver as an example of the required options.
	|
	| Supported Drivers: "local", "ftp", "s3", "rackspace"
	|
	*/

	'disks' => [

		'local' => [
			'driver' => 'local',
			'root' => storage_path('app'),
			'visibility' => 'public',
			'directory_visibility' => 'public',
			'permissions' => [
				'file' => [
					'public' => 0664,
					'private' => 0600,
				],
				'dir' => [
					'public' => 0775,
					'private' => 0700,
				],
			],
		],

		'public' => [
			'driver' => 'local',
			'root' => storage_path('app/public'),
			'url' => env('APP_URL') . '/storage',
			'visibility' => 'public',
			'directory_visibility' => 'public',
			'permissions' => [
				'file' => [
					'public' => 0664,
					'private' => 0600,
				],
				'dir' => [
					'public' => 0775,
					'private' => 0700,
				],
			],
		],

		's3' => [
			'driver' => 's3',
			'key' => env('AWS_KEY'),
			'secret' => env('AWS_SECRET'),
			'region' => env('AWS_REGION'),
			'bucket' => env('AWS_BUCKET'),
			'url' => env('AWS_URL'),
			'endpoint' => env('AWS_ENDPOINT'),
			'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
			'throw' => true,
		],

	],

];
