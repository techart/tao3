<?php

namespace TAO;

use Illuminate\Notifications\Notifiable;
use TAO\Notifications\SiteError;

class ErrorsNotifier
{
	use Notifiable;

	/**
	 * @param \Exception $exception
	 */
	public function run($exception)
	{
		if ($this->isEnabled()) {
			$this->notify(new SiteError($exception));
		}
	}

	public function routeNotificationForMail()
	{
		return $this->recipientEmails();
	}

	public function routeNotificationForSlack()
	{
		return $this->slackWebhookUrl();
	}

	/**
	 * @return bool
	 */
	protected function isEnabled()
	{
		return $this->hasRecipients() && \App::environment('prod');
	}

	protected function config($name, $default = null)
	{
		return config("app.errors.notification.$name", $default);
	}

	/**
	 * @return array|null
	 */
	protected function recipientEmails()
	{
		return $this->config('email');
	}

	/**
	 * @return string|null
	 */
	protected function slackWebhookUrl()
	{
		return $this->config('slack_webhook_url');
	}

	/**
	 * @return \Illuminate\Config\Repository|mixed
	 */
	protected function hasRecipients()
	{
		return !is_null($this->recipientEmails()) || !is_null($this->slackWebhookUrl());
	}
}
