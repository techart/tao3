<?php

namespace TAO\Foundation;

class Utils
{
	public function humanSize($size)
	{
		if ($size >= 10485760) {
			return ((int)round($size / 1048576)) . 'M';
		}
		if ($size >= 1048576) {
			return number_format($size / 1048576, 1) . 'M';
		}
		if ($size >= 10240) {
			return ((int)round($size / 1024)) . 'K';
		}
		if ($size >= 1024) {
			return number_format($size / 1024, 1) . 'K';
		}
		return $size . 'B';
	}
}