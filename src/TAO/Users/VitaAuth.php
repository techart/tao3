<?php

namespace TAO\Users;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

class VitaAuth extends \TAO\Controller
{
	use AuthenticatesUsers;
	
	public function login($data)
	{
		$data = app('tao.utils')->dataDecode($data);
		$token = $data['t'] ?? false;
		$redirect = $data['r'] ?? false;
		
		if (!$token || !$redirect) {
			return $this->pageNotFound();
		}
		
		$dataUrl = config('auth.vita_data_url', 'http://auth.techart.ru/data/')."{$token}/";
		$data = app('tao.http')->getJSON($dataUrl);
		if (!$data && !isset($data['id'])) {
			return $this->pageNotFound();
		}
		$user = $this->createUser($data);
		
		$creds = array(
			'email' => $user->email,
			'password' => '~',
		);
		
		if ($this->guard()->attempt($creds, 0)) {
			return redirect($redirect);
		}
		
		dd($data);
	}
	
	public function logout()
	{
		$this->guard()->logout();
		app('request')->session()->invalidate();
		$url = config('auth.vita_logout_url', 'http://auth.techart.ru/logout/');
		return redirect($url);
	}
	
	protected function createUser($data)
	{
		$user = \TAO::datatype('users')->find($data->id);
		if (!$user) {
			$user = \TAO::datatype('users')->newInstance();
		}
		
		foreach(['id', 'name', 'email', 'nomination', 'office_id', 'techart_dep', 'company', 'agent_id'] as $key) {
			$user->$key = $data->$key;
		}
		$user->is_admin = in_array('root', $data->groups);
		$user->password = bcrypt('~');
		$user->groups = serialize($data->groups);
		$user->save();
		return $user;
	}
	
	public static function authUrl($extra = [])
	{
		$req = app('request');
		$authUrl = config('auth.vita_client_url', '/users/vita/login/');
		if (starts_with($authUrl, '/')) {
			$authUrl = $req->getScheme() . '://' . $req->getHost() . $authUrl;
		}
		$returnUrl = $req->getUri();
		$data = array_merge(array(
			'a' => $authUrl,
			'r' => $returnUrl,
		), $extra);
		$data = app('tao.utils')->dataEncode($data);
		return config('auth.vita_server_url', "http://auth.techart.ru/to/")."{$data}/";
	}
	
	public static function process($extra = [])
	{
		return redirect(self::authUrl($extra));
	}
}
