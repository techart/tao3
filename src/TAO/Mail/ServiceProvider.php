<?php

namespace TAO\Mail;
use Illuminate\Mail\MailServiceProvider;

class ServiceProvider extends MailServiceProvider
{
	public function register()
	{
		parent::register();
		
		app('mail.manager')->extend('php', function () {
			return app('tao.mail.transport');
		});
	}
}