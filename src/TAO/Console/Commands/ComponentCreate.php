<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;
use TAO\Component\ComponentBlankGenerator;

class ComponentCreate extends Command
{
	protected $signature = 'component:create {componentCode} {componentName?}';

	protected $description = 'Создает каркас компонента для tao3 с основной структурой. Параметры:
		componentCode - код компонента, должен содержать только цифры, буквы и знак дефиса;
		componentName - название компонента, должен содержать только цифры и буквы. Необязательный параметр, если
			не указан, то генерируется из кода.';

	public function handle()
	{
		$generator = app(ComponentBlankGenerator::class);
		$generator->run($this->argument('componentCode'), $this->argument('componentName'));
	}
}
