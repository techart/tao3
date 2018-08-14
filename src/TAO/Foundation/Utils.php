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
		if ($m = \TAO::regexp('{(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+):(\d+)}', $date)) {
			return new \DateTime("{$m[3]}-{$m[2]}-{$m[1]} {$m[4]}:{$m[5]}:{$m[6]}");
		}
		if ($m = \TAO::regexp('{(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+)}', $date)) {
			return new \DateTime("{$m[3]}-{$m[2]}-{$m[1]} {$m[4]}:{$m[5]}");
		}
		if ($m = \TAO::regexp('{(\d+)\.(\d+)\.(\d+)}', $date)) {
			return new \DateTime("{$m[3]}-{$m[2]}-{$m[1]}");
		}

		$date = strtotime($date);

		if ($date) {
			return new \DateTime(date('Y-m-d H:i:s', $date));
		}

		return new \DateTime('now');
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

	public function dataEncode($data)
	{
		$data = serialize($data);
		$data = base64_encode($data);
		$data = preg_replace('{==$}', '(2)', $data);
		$data = preg_replace('{=$}', '(1)', $data);
		$data = str_replace('+', '(p)', $data);
		$data = str_replace('/', '(s)', $data);
		return $data;
	}

	public function dataDecode($data)
	{
		$data = str_replace('(s)', '/', $data);
		$data = str_replace('(p)', '+', $data);
		$data = str_replace('(1)', '=', $data);
		$data = str_replace('(2)', '==', $data);
		$data = base64_decode($data);
		$data = unserialize($data);
		return $data;
	}
}
