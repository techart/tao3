<?php

namespace TAO\Users;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends \TAO\Controller
{

	use AuthenticatesUsers;

	public function __construct()
	{
		$this->middleware('guest', ['except' => 'logout']);
	}

	protected function redirectTo()
	{
		return config('auth.redirect_after_login', '/users/home/');
	}

	public function showLoginForm()
	{
		return view('users ~ login');
	}

	protected function setupSocialDriver($driver)
	{
		$cfg = config("services.{$driver}");

		if (isset($cfg['login_handle'])) {
			$cfg['login'] = true;
			\Event::listen(\SocialiteProviders\Manager\SocialiteWasCalled::class, $cfg['login_handle']);
		}

		if (!isset($cfg['login']) || !$cfg['login']) {
			return false;
		}

		$callback = trim(config('app.url'), '/') . "/login/social/{$driver}/callback/";

		app()->config->set("services.{$driver}.redirect", $callback);
		return true;
	}

	public function redirectToProvider($driver)
	{
		if (!$this->setupSocialDriver($driver)) {
			return \TAO::pageNotFound();
		}
		return \Socialite::driver($driver)->redirect();
	}

	public function handleProviderCallback($driver)
	{
		$this->setupSocialDriver($driver);
		$request = app()->request();

		$userData = \Socialite::driver($driver)->user();
		$sData = serialize($userData);
		$name = $userData->getName();
		$email = $userData->getEmail();

		if (empty($email)) {
			return $this->sendFailedLoginResponse($request);
		}

		$name = empty($name) ? $email : $name;

		$user = \TAO::datatype('users')->where('email', $email)->first();
		if (!$user) {
			$user = \TAO::datatype('users')->newInstance();
			$user->field('name')->set($name);
			$user->field('email')->set($email);
			$user->field('password')->set(bcrypt('~'));
			$user->field('social')->set($driver);
			$user->field('social_info')->set($sData);
			$user->setupAfterSocialAuth($userData);
			$user->save();
			$user->setupAfterSocialAuth2($userData);
		} else {
			$user->field('social')->set($driver);
			$user->field('social_info')->set($sData);
			$user->save();
		}

		if ($this->guard()->attempt(['email' => $email, 'password' => '~'], 1)) {
			return redirect('/users/home/');
		}
		return $this->sendFailedLoginResponse($request);
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
