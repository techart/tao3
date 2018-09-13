<?php
if (!function_exists('tao_lang')) {
	function tao_lang($name, $locale, $values = [])
	{
		if (preg_match('/^(.+?):(.+?)$/', $name, $m)) {
			// from composer package
			$path = realpath(__DIR__ . '/../../' . $m[1]) . "/resources/lang/{$locale}/{$m[2]}.php";
		} else {
			// from tao3
			$path = realpath(__DIR__ . '/../resources') . "/lang/{$locale}/{$name}.php";
		}
		if (is_file($path)) {
			return array_replace_recursive(include($path), $values);
		}
		return $values;
	}
}
