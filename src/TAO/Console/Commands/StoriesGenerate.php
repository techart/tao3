<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class UsersCreate
 * @package TAO\Console\Commands
 */
class StoriesGenerate extends Command
{
	const ESC_RED_START = "\e[33m";
	const ESC_FINISH = "\e[0m";

	protected $description = 'Генерирует "истории" блоков для Storybook';


	public function __construct()
	{
		$lines = [
			'stories:generate',
		];

		$params = \Techart\Frontend\Storybook::getParams();
		foreach ($params as $name => $data) {
			if ('help' === $name) {
				continue;
			}

			$default = $data['default'] ?? '';
			$lines[] = "\t\t{--{$name}={$default} : {$data['info']} }";
		}

		$this->signature = implode(PHP_EOL, $lines);

		parent::__construct();
	}


	/**
	 * Основная логика команды - вызов генератора историй для Storybook из techart/frontend-api
	 */
	public function handle()
	{
		$rootPath = $_SERVER['PWD'];

		$_overwrite = $this->option('overwrite', 'no');
		$_only = $this->option('only');

		$storybook = new \Techart\Frontend\Storybook($rootPath);
		$storybook->Run([
			'all_overwrite' => $_overwrite ? ('yes' === $_overwrite) : null,
			'only_blocks' => $_only ? explode(',', $_only) : [],
		]);
	}

}
