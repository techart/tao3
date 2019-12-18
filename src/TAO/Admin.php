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
			$title = $datatype->adminMenuTitle();
			$url = '/admin/datatype/' . $code;
			if (is_string($section)) {
				if (!isset($menu[$section])) {
					$menu[$section] = array(
						'title' => $section,
						'url' => $url,
						'sub' => array(),
					);
				}
				$menu[$section]['sub'][] = array(
					'title' => $title,
					'url' => $url,
					'access' => "datatype.$code::accessAdminMenuItem"
				);
			}
		}
		return $menu;
	}
}