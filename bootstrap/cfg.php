<?php

function tao_cfg($name, $values = [])
{
	$path = realpath(__DIR__ . '/../config') . "/{$name}.php";
	if (is_file($path)) {
		$out = include($path);
		$out = tao_cfg_merge($out, $values);
		return $out;
	}
	return $values;
}


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
