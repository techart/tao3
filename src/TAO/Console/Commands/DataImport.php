<?php

namespace TAO\Console\Commands;

use Illuminate\Console\Command;

class DataImport extends Command
{
	protected $signature = 'data:import {datatype}';

	protected $description = 'Import datatype data';

	public function handle()
	{
		$model = \TAO::datatype($this->argument('datatype'));
		$model->getQuery()->delete();
		while ($line = fgets(STDIN)) {
			if ($line = trim($line)) {
				if ($m = \TAO::regexp('{^@(\d+)$}', $line)) {
					$id = (int)$m[1];
					$item = $model->newInstance();
					$item->id = $id;
					$item->save();
					$item = $this->importItem($item);
					$item->save();
				} else {
					dd('Error', $line);
				}
			}
		}
	}

	protected function importItem($item)
	{
		while ($line = fgets(STDIN)) {
			if ($line = trim($line)) {
				if ($line == '@@') {
					break;
				} elseif ($m = \TAO::regexp('{^!([a-z0-9_]+)=(.*)$}i', $line)) {
					$field = trim($m[1]);
					$value = trim($m[2]);
					$item->field($field)->dataImport($value);
				} elseif ($m = \TAO::regexp('{^!([a-z0-9_]+)$}i', $line)) {
					$field = trim($m[1]);
					$value = '';
					while ($line = fgets(STDIN)) {
						if (trim($line) == '!!') {
							break;
						}
						$value .= $line;
					}
					$item->field($field)->dataImport($value);
				}
			}
		}
		return $item;
	}
}