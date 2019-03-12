<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;
use TAO\Support\ServiceFoldersCreator;

class ServiceFoldersCreate extends Command
{
	protected $signature = 'service_folders:create';

	protected $description = 'Create application\'s service folders if not exists';

	public function handle()
	{
		(app(ServiceFoldersCreator::class))->run();
	}
}
