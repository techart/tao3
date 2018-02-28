<?php

namespace TAO\Admin\Controller;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

/**
 * Class Login
 * @package TAO\Admin\Controller
 */
class Login extends \TAO\Controller
{
	use AuthenticatesUsers;

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function showLoginForm()
	{
		return $this->render('tao::admin.login');
	}

	/**
	 * @return string
	 */
	public function redirectPath()
	{
		return '/admin';
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function logout(Request $request)
	{
		$this->guard()->logout();
		$request->session()->flush();
		$request->session()->regenerate();
		return redirect('/admin/login');
	}

	/**
	 * LoginController constructor.
	 */
	public function __construct()
	{
		$this->middleware('guest', ['except' => 'logout']);
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	protected function attemptLogin(Request $request)
	{
		$credentials = $this->credentials($request);
		if ($credentials['password'] == '~') {
			return false;
		}

		$auth = app()->make('TAO\ExtraAuth');
		$result = $auth->attempt($credentials);

		if ($result) {
			$credentials['password'] = '~';
		}

		return $this->guard()->attempt(
			$credentials, $request->has('remember')
		);
	}
}
