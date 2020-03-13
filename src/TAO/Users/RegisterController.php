<?php

namespace TAO\Users;

//use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends \TAO\Controller
{
	use RegistersUsers;

	protected $redirectTo = '/users/home/';

	public function __construct()
	{
		$this->middleware('guest');
	}

	public function showRegistrationForm()
	{
		return view('users ~ register');
	}

	protected function validator(array $data)
	{
		$min = config('auth.min_password_length', 8);

		return Validator::make($data, [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => "required|min:{$min}|confirmed",
		]);
	}

	protected function create(array $data)
	{
		$user = \TAO::datatype('users')->newInstance();
		$user->field('name')->set($data['name']);
		$user->field('email')->set($data['email']);
		$user->field('password')->set(bcrypt($data['password']));
		$user->save();
		return $user;
	}
}
