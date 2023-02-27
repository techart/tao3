<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;

class PhpLintRun extends Command
{
	protected $signature = 'php-lint:run';

	protected $description = 'Run php-lint';

	public function handle()
	{
		$this->comment('Php-lint start...');
		$path = exec('pwd');
		exec("cd $path/php-tooling && php ./vendor/bin/phpcs ../ --colors", $output);
		echo implode("\n", $output);
	}
}