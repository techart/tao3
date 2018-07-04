<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;

class DataExport extends Command
{
	protected $signature = 'data:export {datatype}';

	protected $description = 'Export datatype data';

	public function handle()
	{
		$model = \TAO::datatype($this->argument('datatype'));
		foreach ($model->orderBy('id', 'asc')->get() as $row) {
			print $row->dataExport();
		}
	}
}