<?php

namespace TAO\Support;

class ServiceFoldersCreator
{
	protected $folders = [
		'storage/app/public',
		'storage/app/vars',
	];

	public function run()
	{
		foreach ($this->folders as $folder) {
			$folderPath = $this->folderPath($folder);
			if (!\File::exists($folderPath)) {
				\File::makeDirectory($folderPath, 0775, true);
			}
		}
	}

	protected function folderPath($folder)
	{
		return base_path($folder);
	}
}
