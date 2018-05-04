<?php

namespace TAO\Foundation;

use Carbon\Carbon;

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

	public function dateTime($date = false)
	{
		if ($date === false) {
			return new \DateTime('now');
		}
		if (\TAO::regexp('{^\d+$}', trim($date))) {
			return new \DateTime(date('Y-m-d H:i:s', $date));
		}
		if ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)$}', $date)) {
			return new \DateTime("{$m[3]}-{$m[2]}-{$m[1]}");
		}
		if ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+)$}', $date)) {
			return new \DateTime("{$m[3]}-{$m[2]}-{$m[1]} - {$m[4]}:{$m[5]}:0");
		}
		if ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+):(\d+)$}', $date)) {
			return new \DateTime("{$m[3]}-{$m[2]}-{$m[1]} - {$m[4]}:{$m[5]}:{$m[5]}");
		}
		return new \DateTime($date);
	}

	public function date($format = false, $date = false)
	{
		$dt = $this->dateTime($date);
		if ($format === false) {
			return $dt->getTimestamp();
		}
		return $dt->format($format);
	}

	public function carbon($date = false)
	{
		return Carbon::instance($this->dateTime($date));
	}
}