<?php

namespace TAO;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function layout()
	{
		return 'layouts.app';
	}

	public function setup()
	{
		\TAO::useLayout($this->layout());
		\TAO::setController($this);
	}

	protected function render($template, $context = array())
	{
		$context['controller'] = $this;
		return view($template, $this->setupContextForRender($context));
	}

	protected function setupContextForRender($context)
	{
		return $context;
	}

	protected function renderWithinLayout($template, $context = array())
	{
		$context['controller'] = $this;
		return \TAO::renderWithinLayout($template, $context);
	}

	protected function urlLogin()
	{
		return '/login';
	}

	protected function accessAction($method, $parameters)
	{
		return true;
	}

	protected function beforeAction($method, $parameters)
	{

	}

	protected function renderAccessDenied()
	{
		return view('auth ~ denied');
	}

	protected function accessDenied()
	{
		if (!\Auth::user()) {
			return redirect($this->urlLogin());
		}
		return $this->renderAccessDenied();
	}

	public function callAction($method, $parameters)
	{
		$this->setup();
		$rc = $this->accessAction($method, $parameters);
		if ($rc === true) {
			$this->beforeAction($method, $parameters);
			return parent::callAction($method, $parameters);
		}
		if ($rc === false) {
			return $this->accessDenied();
		}
		return $rc;
	}

	public function json($m)
	{
		return response(json_encode($m), 200, ['Content-Type' => 'application/json']);
	}

	public function pageNotFound()
	{
		return response($this->render('404'), 404);
	}


}
