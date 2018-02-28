<?php

namespace TAO;

class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{
	public function to($path, $extra = [], $secure = null)
	{
		if ($uw = \TAO::datatype('urlrewriter', false)) {
			$url = \TAO\Urls::sortUrl($path);
			$path = $uw->getReplaced($url);
		}
		return parent::to($path, $extra, $secure);
	}

	public function isValidUrl($path)
	{
		$v = parse_url($path);
		if (isset($v['path']) && !empty($v['path'])) {
			$path = $v['path'];
			if ($path[0] == '/') {
				return true;
			}
		}
		return parent::isValidUrl($path);
	}

}