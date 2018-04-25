<?php

namespace TaoTests;

use TAO\App\Providers\RouteServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	protected $appConfig;

	protected function setUp()
	{
		parent::setUp();

		if ($datatypes = $this->getDatatypes()) {
			foreach ($datatypes as $datatypeCode => $datatypeClass) {
				\TAO::addDatatype($datatypeCode, $datatypeClass);
			}
		}
		$this->withFactories(__DIR__ . '/factories');
	}

	protected function resolveApplicationConfiguration($app)
	{
		parent::resolveApplicationConfiguration($app);

		$path = './config';
		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$configName = substr($entry, 0, strpos($entry, '.'));
					$configPath = $path . '/' . $entry;
					$app['config'][$configName] = include $configPath;
				}
			}
			closedir($handle);
		}
	}



	protected function resolveApplication()
	{
		$appPath = $this->getBasePath();
		$vendorPath = realpath(__DIR__ . '/../vendor');
		$app = require './bootstrap/app.php';

		return tap($app, function ($app) {
			$app->bind(
				'Illuminate\Foundation\Bootstrap\LoadConfiguration',
				'Orchestra\Testbench\Bootstrap\LoadConfiguration'
			);
		});
	}

	protected function getPackageProviders($app)
	{
		return $this->appConfig('providers');
	}

	protected function getDatatypes()
	{
		return [];
	}

	protected function appConfig($configName = null)
	{
		if (is_null($this->appConfig)) {
			$this->appConfig = include './config/app.php';
		}
		return is_null($configName) ? $this->appConfig : $this->appConfig[$configName];
	}

	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);
	}
}