<?php

namespace TAO\Controller;

use TAO\Controller;

class Utils extends Controller
{
	public function taoversion()
	{
		return view('version');
	}
}
