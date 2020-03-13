<?php

namespace TAO\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends ResetPasswordNotification
{
	public function toMail($notifiable)
	{
		return (new MailMessage())
			->subject(config('messages.reset-password-subject', 'Сброс пароля'))
			->from(config('app.mail-from', 'robot@' . request()->getHost()))
			->view('email.reset-password', [
				'token' => $this->token,
				'email' => $notifiable->getEmailForPasswordReset(),
			]);
	}
}