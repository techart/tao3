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
		return $this->hasRecipients() && app()->environment('prod');
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
		return $this->isEmailEnabled() || $this->isSlackEnabled();
	}

	protected function isEmailEnabled()
	{
		return !is_null($this->recipientEmails());
	}

	protected function isSlackEnabled()
	{
		return !is_null($this->slackWebhookUrl());
	}

	public function enabledChannels()
	{
		$channels = [];
		if ($this->isSlackEnabled()) {
			$channels[] = 'slack';
		}
		if ($this->isEmailEnabled()) {
			$channels[] = 'mail';
		}
		return $channels;
	}
}
