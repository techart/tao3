<?php
if (!function_exists('who_calls')) {
	function who_calls($n = 'all')
	{
		$out = [];
		$data = debug_backtrace();
		foreach($data as $item) {
			$out[] = "({$item['line']}) {$item['file']}";
		}
		dump($out);
	}
}