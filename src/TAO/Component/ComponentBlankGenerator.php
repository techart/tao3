<?php

namespace TAO\Component;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use TAO\Exception;
use TAO\Text\StringTemplate;

/**
 * Генератор для создания каркаса отдельного компонента tao3.
 *
 * Class ComponentBlankGenerator
 * @package TAO\Component
 */
class ComponentBlankGenerator
{
	protected $componentName;
	protected $componentCode;

	const BLANK_REPOSITORY_URL = 'https://gitlab.techart.ru/laravel/component-blank/-/archive/master/component-blank-master.zip';

	/**
	 * @param string $componentCode
	 * @param string|null $componentName
	 * @param string|null $dir
	 * @throws Exception
	 */
	public function run($componentCode, $componentName = null, $dir = null)
	{
		$this->componentCode = $componentCode;
		$this->componentName = $componentName;

		$this->prepareComponentName();
		if (!$this->checkComponentName()) {
			throw new Exception('Название компонента может содержать только анг. буквы, цифры и дефис.');
		}
		$dir = $dir ?? $this->defaultDirectory();
		$componentDir = $this->loadComponentBlank($dir);
		$this->assignComponentName($componentDir);
	}

	public function loadComponentBlank($dir)
	{
		if (!File::exists($dir)) {
			File::makeDirectory($dir, 0775, true);
		}
		$path = rtrim($dir, '/') . '/rep.zip';
		$fileHandle = fopen($path, 'w');
		$client = new Client();
		if ($client->get(static::BLANK_REPOSITORY_URL, ['save_to' => $fileHandle])) {
			$zip = new \ZipArchive();
			$res = $zip->open($path);
			if ($res === true) {
				$zip->extractTo($dir);
				$zip->close();
				unlink($path);
				$componentDir = $dir . '/' . $this->componentCode();
				File::moveDirectory($dir . '/component-blank-master', $dir . '/' . $this->componentCode());
				return $componentDir;
			} else {
				throw new Exception('Не удалось распаковаьт архив репозитория');
			}
		}
	}

	public function assignComponentName($componentDir)
	{
		foreach (File::directories($componentDir) as $dir) {
			$this->assignComponentNameToDir($dir);
		}
		foreach (File::allFiles($componentDir) as $file) {
			$this->assignComponentNameToFile($file);
		}
	}

	/**
	 * @param SplFileInfo $fileInfo
	 */
	public function assignComponentNameToFile($fileInfo)
	{
		$oldContent = file_get_contents($fileInfo->getPathname());
		$newContent = $this->assignComponentNameToStr($oldContent);
		if ($oldContent !== $newContent) {
			file_put_contents($fileInfo->getPathname(), $newContent);
		}

		File::move($fileInfo->getPathname(),
			$this->assignComponentNameToStr($fileInfo->getPathname()));
	}

	public function assignComponentNameToDir($dir)
	{
		foreach (File::directories($dir) as $subDir) {
			$this->assignComponentNameToDir($subDir);
		}
		$dirNew = $this->assignComponentNameToStr($dir);
		if ($dir != $dirNew) {
			File::moveDirectory($dir, $dirNew);
		}
	}

	public function assignComponentNameToStr($str)
	{
		return StringTemplate::process($str, [
			'component_code' => $this->componentCode(),
			'ComponentName' => $this->componentName(),
		], '/%(.+?)%/');
	}

	protected function prepareComponentName()
	{
		$this->componentCode = strtolower(str_replace('_', '-', $this->componentCode()));
		if (!$this->componentName()) {
			$this->componentName = $this->generateNameFromCode();
		}
	}

	protected function checkComponentName()
	{
		return preg_match('/[^a-z0-9-]/', $this->componentCode()) == 0
			&& preg_match('/[^a-zA-Z0-9]/', $this->componentName()) == 0;
	}

	protected function defaultDirectory()
	{
		return getcwd() . '/repository';
	}

	protected function generateNameFromCode()
	{
		$nameChunks = explode('-', $this->componentCode());
		return implode('', array_map(function ($value) {
			return Str::ucfirst($value);
		}, $nameChunks));
	}

	/**
	 * @return string
	 */
	protected function componentCode()
	{
		return $this->componentCode;
	}

	/**
	 * @return string
	 */
	protected function componentName()
	{
		return $this->componentName;
	}
}
