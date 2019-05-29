<?php

namespace TAO\Controller;

use TAO\Controller;

class MainPage extends Controller
{
	public function index()
	{
		return view('main');
	}
}
