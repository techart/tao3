<?php

namespace TAO\Components\Protect;

use Illuminate\Support\HtmlString;

class Utils
{
	public function timeField($time = 5)
	{
		$field = '_tao_form_info';
		$data = array(
			'min_time' => time() + $time,
		);
		$info = \Crypt::encrypt($data);
		return new HtmlString("<input type='hidden' name='{$field}' value='{$info}'>");
	}
}