<?php

namespace TAO\Mail;
use Illuminate\Mail\MailServiceProvider;

class ServiceProvider extends MailServiceProvider
{
	protected function registerSwiftTransport()
	{
		parent::registerSwiftTransport();
		app('swift.transport')->extend('php', function () {
			return app('tao.mail.transport');
		});
	}
}