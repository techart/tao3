<?php

namespace TaoTests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	protected $appConfig;

	protected function setUp(): void
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

		$path = realpath(__DIR__ . '/../config');
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
		$vendorPath = $this->vendorPath();
		$app = require realpath(__DIR__ . '/../bootstrap/app.php');

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
			$this->appConfig = include realpath(__DIR__ . '/../config/app.php');
		}
		return is_null($configName) ? $this->appConfig : $this->appConfig[$configName];
	}

	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => '',
		]);
	}

	protected function vendorPath()
	{
		return realpath(__DIR__ . '/../vendor');
	}
}
