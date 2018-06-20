<?php

namespace TAO\Config;

use TAO\Text\StringTemplate;

class ConfigFileGenerator
{
	public static function run($name, $package = null)
	{
		$filePath = config_path($name . '.php');
		if (!\File::exists($filePath)) {
			$content = StringTemplate::process(self::tpl(), ['name' => $package ? $package . ':' . $name : $name]);
			file_put_contents($filePath, $content);
		}
	}

	protected function fileName()
	{
		return $this->name . '.php';
	}

	protected static function tpl()
	{
		return "<?php\n\nreturn tao_cfg('{name}');\n";
	}
}