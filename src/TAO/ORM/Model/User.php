<?php

namespace TAO\ORM\Model;

use Illuminate\Notifications\Notifiable;
use TAO\ORM\Abstracts\User as AbstractUser;

class User extends AbstractUser
{
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	public function loginUrl()
	{
		return '/users/login/';
	}

	public function loginController()
	{
		return \TAO\Users\LoginController::class;
	}

	public function registerController()
	{
		return \TAO\Users\RegisterController::class;
	}
}


