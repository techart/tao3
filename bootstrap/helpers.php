<?php

if (!function_exists('who_calls')) {
	function who_calls($limit = 0)
	{
		$out = [];
		$data = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit);
		foreach ($data as $item) {
			$m = '';
			if (isset($item['line'])) {
				$m .= "({$item['line']}) ";
			}
			if (isset($item['class'])) {
				$m .= "{$item['class']}::";
			} elseif (isset($item['file'])) {
				$file = preg_replace('{^.*/src/}', '', $item['file']);
				$m .= "{$file} --- ";
			}
			if (isset($item['function'])) {
				$m .= "{$item['function']}()";
			}
			$out[] = $m;
		}
		dump($out);
	}
}

if (!function_exists('trait_used')) {
	/**
	 * @param object|string $object
	 * @param string $traitName
	 * @return bool
	 */
	function trait_used($object, $traitName)
	{
		return in_array($traitName, class_uses_recursive($object));
	}
}

if (!function_exists('dt')) {
	function dt($code)
	{
		return \TAO::datatype($code);
	}
}

if (!function_exists('protect_field')) {
	function protect_field($time = 5)
	{
		return app(\TAO\Components\Protect\Utils::class)->timeField($time);
	}
}


if (!function_exists('insertions')) {
	function insertions($src)
	{
		return \TAO\Text::process($src, 'insertions');
	}
}
