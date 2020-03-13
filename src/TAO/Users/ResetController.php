<?php

namespace TAO\Users;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Database\Schema\Blueprint;

class ResetController extends \TAO\Controller
{
	use ResetsPasswords;

	protected $redirectTo = '/users/home/';

	protected function rules()
	{
		$min = config('auth.min_password_length', 8);

		return [
			'token' => 'required',
			'email' => 'required|email',
			'password' => "required|confirmed|min:{$min}",
		];
	}

	protected function validationErrorMessages()
	{
		$min = config('auth.min_password_length', 8);

		return [
			'passwords.token' => 'Недействительный токен',
			'email.required' => 'Введите E-Mail',
			'email.email' => 'Некорректный E-Mail',
			'password.required' => 'Введите новый пароль',
			'password.confirmed' => 'Пароль и его копия не совпадают',
			'password.min' => "Минимальная длина пароля - {$min} символов",
		];
	}
}
