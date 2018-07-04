<?php

namespace TAO\Notifications;

use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use TAO\Text\StringTemplate;

class SiteError extends Notification
{
	/**
	 * @var \Exception
	 */
	protected $exception;

	/**
	 * @param \Exception $exception
	 * @return void
	 */
	public function __construct($exception)
	{
		$this->exception = $exception;
	}

	public function via($notifiable)
	{
		return ['mail', 'slack'];
	}

	/**
	 * Создание почтового сообщения об ошибке
	 *
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail()
	{
		return (new MailMessage())
			->subject($this->mailSubject())
			->from($this->mailFrom())
			->view('email.site-error', $this->mailTemplateContext());
	}

	/**
	 * Создание сообщения для Slack
	 *
	 * @return SlackMessage
	 */
	public function toSlack()
	{
		return (new SlackMessage())
			->content($this->slackMessageContent())
			->attachment($this->slackMessageAttachment());
	}

	protected function config($name, $default = null)
	{
		return config("app.errors.notification.$name", $default);
	}

	protected function mailSubject()
	{
		return StringTemplate::process($this->config('subject'), [
			'url' => '/' . request()->path() . '/',
			'fullUrl' => request()->getUri(),
			'name' => config('app.name')
		]);
	}

	protected function defaultSubject()
	{
		return '{name}: Ошибка по адресу {fullUrl}';
	}

	protected function mailFrom()
	{
		return $this->config('from', $this->defaultMailFrom());
	}

	protected function defaultMailFrom()
	{
		return 'robot@' . request()->getHost();
	}

	protected function mailTemplateContext()
	{
		return [
			'trace' => $this->fullTraceHtml(),
			'exception' => $this->exception
		];
	}

	protected function fullTraceHtml()
	{
		return (new ExceptionHandler())->getHtml(FlattenException::create($this->exception));
	}

	protected function exceptionShortInfo($exception)
	{
		return $this->exception->getMessage() . $this->exception->getFile() . ':' . $this->exception->getLine();
	}

	protected function slackMessageContent()
	{
		return '<!channel> ' . $this->mailSubject() . "\n\n" . $this->exceptionShortInfo($this->exception);
	}

	protected function slackMessageAttachment()
	{
		$exception = $this->exception;
		return function ($attachment) use ($exception) {
			/** @var SlackAttachment $attachment */
			$attachment->title('Trace')->content($exception->getTraceAsString());
		};
	}
}
