<?php

namespace TAO;

use Illuminate\Contracts\View\Factory as ViewFactory;

class Admin
{
	public function layout()
	{
		return 'admin.layout';
	}

	public function embeddedLayout()
	{
		return 'admin.layout-embedded';
	}

	public function menu()
	{
		$menu = array();
		foreach (\TAO::datatypes() as $code => $datatype) {
			$section = $datatype->adminMenuSection();
			if (is_string($section)) {
				$dtlink = $datatype->adminMenuLink($code);
				if (!isset($menu[$section])) {
					$menu[$section] = array(
						'title' => $section,
						'url' => $dtlink['url'],
						'sub' => array(),
					);
				}
				$menu[$section]['sub'][] = $dtlink;
			}
		}
		return $menu;
	}
}
