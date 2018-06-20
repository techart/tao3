<?php

if (!function_exists('tao_cfg')) {
	function tao_cfg($name, $values = [])
	{
		if (preg_match('/^(.+?):(.+?)$/', $name, $m)) {
			// from composer package
			$path = realpath(__DIR__ . '/../../' . $m[1]) . "/config/{$m[2]}.php";
		} else {
			// from tao3
			$path = realpath(__DIR__ . '/../config') . "/{$name}.php";
		}
		if (is_file($path)) {
			$out = include($path);
			$out = tao_cfg_merge($out, $values);
			return $out;
		}
		return $values;
	}
}

if (!function_exists('tao_cfg_merge')) {
	function tao_cfg_merge($out, $values = [])
	{
		foreach ($values as $k => $value) {
			if (is_array($value) && isset($out[$k]) && is_array($out[$k])) {
				if (\TAO\Type\Collection::isIndexed($value) && \TAO\Type\Collection::isIndexed($out[$k])) {
					$out[$k] = array_merge($out[$k], $value);
				} else {
					$out[$k] = tao_cfg_merge($out[$k], $value);
				}
			} elseif (is_null($value) && isset($out[$k])) {
				unset($out[$k]);
			} else {
				$out[$k] = $value;
			}
		}
		return $out;
	}
}
