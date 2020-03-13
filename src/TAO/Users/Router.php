<?php

namespace TAO\Users;

class Router extends \TAO\Router
{
	public function routes()
	{
		if (!\TAO::isCLI() && config('auth.public.login', false)) {
			/**
			 * @var User $datatype
			 */
			$datatype = \TAO::datatype('users');
			$controller = '\\' . $datatype->loginController();
			$urlLogin = $datatype->loginUrl();
			\Route::get($urlLogin, "{$controller}@showLoginForm");
			\Route::post($urlLogin, array('as' => 'login', 'uses' => "{$controller}@login"))->name('login');
			\Route::get('/users/logout/', "{$controller}@logout");
			\Route::get('/login/social/{driver}/', "{$controller}@redirectToProvider");
			\Route::get('/login/social/{driver}/callback/', "{$controller}@handleProviderCallback");
		}

		if (!\TAO::isCLI() && config('auth.public.register', false)) {
			/**
			 * @var User $datatype
			 */
			$datatype = \TAO::datatype('users');
			$controller = '\\' . $datatype->registerController();
			$urlRegister = '/users/register/';
			\Route::get($urlRegister, "{$controller}@showRegistrationForm");
			\Route::post($urlRegister, "{$controller}@register")->name('register');
		}

		if (!\TAO::isCLI() && config('auth.public.reset', false)) {
			/**
			 * @var User $datatype
			 */
			$datatype = \TAO::datatype('users');
			$resetController = '\\' . $datatype->resetController();
			$sendController = '\\' . $datatype->sendResetEmailsController();

			\Route::post('password/email', $sendController . '@sendResetLinkEmail')->name('password.email');
			\Route::get('password/reset', $sendController . '@showLinkRequestForm')->name('password.request');
			\Route::get('password/reset/{token}', $resetController . '@showResetForm')->name('password.reset');
			\Route::post('password/reset', $resetController . '@reset');
		}
	}
}