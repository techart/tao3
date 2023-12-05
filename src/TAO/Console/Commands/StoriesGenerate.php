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

	protected $signature = 'stories:generate
		{--overwrite=yes : указывает генератору, следует ли перезаписывать уже существующие файлы историй; возможные значения - yes или no }
		{--only= : указывает генератору, для каких блоков следует перегенерировать истории; принимает имена блоков как для вызова renderBlock(), несколько блоков можно указать через запятую}
	';

	protected $description = 'Генерирует "истории" блоков для Storybook';


	/**
	 *
	 */
	public function handle()
	{
		$_overwrite = $this->option('overwrite', 'no');
		$_only = $this->option('only');

		$all_overwrite = $_overwrite ? ('yes' === $_overwrite) : null;

		if ($all_overwrite) {
			echo PHP_EOL, static::ESC_RED_START, "ВНИМАНИЕ!!! Включен режим автоматической перезаписи всех файлов для историй блоков!", static::ESC_FINISH, PHP_EOL, PHP_EOL;
		}

		$only_blocks = $_only ? explode(',', $_only) : [];


		$rootPath = $_SERVER['PWD'];

		// Читаем .workspace_config
		$config = file_get_contents($rootPath . "/.workspace_config");

		// Достаём путь до папки src
		$matches = [];
		preg_match("/^(path_to_frontend|path_to_mordor)(.*)$/im", $config, $matches);
		$srcPath = "{$rootPath}/" . trim($matches[2]) . "/src";

		// Узнаём название проекта
		$matches = [];
		preg_match("/^project\s+(.*)$/im", $config, $matches);
		$projectName = trim($matches[1]);

		// Получаем список всех блоков для генерации
		$jsonFiles = \Techart\Frontend\Storybook::scanStorybookJson("{$srcPath}/block", $only_blocks);

		if (0 < count($jsonFiles)) {
			echo 'Генерация историй Storybook...', PHP_EOL, PHP_EOL;

			foreach($jsonFiles as $jsonFile) {
				try {
					$blockData = json_decode(file_get_contents($jsonFile), true);

					if (!$blockData ||
						!is_array($blockData) ||
						(0 === count($blockData))) {
						echo '!!! Не удалось прочитать данные из файла ', basename($jsonFile), PHP_EOL, PHP_EOL;
						continue;
					}

					$blockName = $blockData['block'];
					echo '... ', $blockName, PHP_EOL;

					$params = [
						'block' => $blockName,
						'section' => isset($blockData['section']) ? $blockData['section'] : $projectName,
						'title' => isset($blockData['title']) ?  $blockData['title'] : $blockName,
						'parameters' => [],
						'htmls' => [],
						'args' => [],
						'controls' => [],
						'flags' => [
							'all_overwrite' => &$all_overwrite,
						],
					];

					$paramNames = [
						'backgrounds',
						'layout',
					];

					foreach ($paramNames as $paramName) {
						if (isset($blockData[$paramName])) {
							$params['parameters'][$paramName] = $blockData[$paramName];
						}
					}

					foreach ($blockData['variants'] as $variantName => $variantParams) {
						$tempVars = [];
						foreach ($variantParams['controls'] as $paramName => $paramData) {
							$_control = null;
							$value = isset($paramData['value']) ? $paramData['value'] : '';
							if (is_array($value)) {
								if (isset($value['control']) &&
									(false === $value['control'])) {
									$_control = false;
									$value = '';
								} else {
									$_control = $value;
									$value = '';
								}
							}

							if (false !== $_control) {
								if (!isset($params['args'][$variantName])) {
									$params['args'][$variantName] = [];
								}
								if (!isset($params['controls'][$variantName])) {
									$params['controls'][$variantName] = [];
								}

								$params['args'][$variantName][$paramName] = $value;

								$value = isset($paramData['name']) ? ['name' => $paramData['name']] : [];
								if ($_control && is_array($_control)) {
									$value = array_merge($_control, $value);
								}
								$params['controls'][$variantName][$paramName] = $value;
							}

							if (isset($paramData['php'])) {
								$tempVars[$paramName] =  eval('return ' . $paramData['php']);
							} else if (isset($paramData['var'])) {
								$tempVars[$paramName] =  $paramData['var'];
							} else {
								$tempVars[$paramName] =  '@' . $paramName . '@';
							}
						}
						$has_with = isset($blockData['with']);
						$params['htmls'][$variantName] = [
							'styles' => $has_with && isset($blockData['with']['styles']) ? $blockData['with']['styles'] : [],
							'scripts' => $has_with && isset($blockData['with']['scripts']) ? $blockData['with']['scripts'] : [],
							'vars' => $tempVars,
							'replaces' => isset($variantParams['replaces']) ? $variantParams['replaces'] : [],
							'php' => isset($paramData['php']) ? $paramData['php'] : '',
						];
					}

					$storybook = new \Techart\Frontend\Storybook($srcPath, $params);
					$storybook->Generate();

					echo '... Ok', PHP_EOL;
				} catch (\Error $e) {
					echo '!!! ОШИБКА: ', $e->getMessage(), PHP_EOL;
				}
				echo PHP_EOL;
			}

			echo 'Генерация окончена!', PHP_EOL;
		} else {
			echo 'Нет блоков, требующих генерации историй Storybook.', PHP_EOL;
		}
	}

}
