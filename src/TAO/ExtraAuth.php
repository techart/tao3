<?php

namespace TAO;

class ExtraAuth
{

	public function attempt($credentials)
	{
		$email = $credentials['email'];
		$password = $credentials['password'];
		$result = $this->process($email, $password);
		if (is_array($result) && isset($result['name'])) {
			$name = $result['name'];
			$users = \TAO::datatype('users')->where('email', $email)->take(1)->get();
			if (count($users) == 0) {
				$user = \TAO::datatype('users')->newInstance();
				$user->field('name')->set($name);
				$user->field('email')->set($email);
				$user->field('password')->set(bcrypt('~'));
				$user->field('social')->set('*extra');
				$user->setupAfterExtraAuth($result);
				$user->save();
				$user->setupAfterExtraAuth2($result);
			}
			return true;
		}
		return false;
	}

	protected function emailToLogin($email)
	{
		$re = config('auth.extra.login', '{^(.+)@techart\.ru$}');
		if (!empty($re)) {
			if ($m = \TAO::regexp($re, $email)) {
				return $m[1];
			}
		}
		return false;
	}

	protected function authUrl()
	{
		return config('auth.extra.url');
	}

	protected function process($email, $password)
	{
		$login = $this->emailToLogin($email);
		if ($login) {
			$url = $this->authUrl();
			if (!empty($url)) {
				$curl = new \Curl\Curl();
				$curl->setBasicAuthentication($login, $password);
				$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
				$curl->get($url);
				if (!$curl->error && $curl->http_status_code == 200) {
					return array(
						'name' => $login,
					);
				}
			}
		}
		return false;
	}

}