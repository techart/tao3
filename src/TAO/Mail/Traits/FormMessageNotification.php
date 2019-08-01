<?php

namespace TAO\Mail\Traits;

use Illuminate\Support\Arr;

trait FormMessageNotification
{
	// TODO: реализовать два класса с одним базовым - для notify и reply, все дублируется
	protected $sendNotify = true;
	protected $notifyEmail = [];
	protected $notifySubject = 'Заполнена форма';
	protected $notifyFrom = '';
	protected $notifyMailable = \TAO\Mail\FormMessageNotifyMail::class;
	protected $notifyHtmlTemplate = 'email.form.html.notify';
	protected $notifyTextTemplate = 'email.form.plain.notify';

	protected $sendReply = true;
	protected $replyEmail = [];
	protected $replySubject = 'Вы заполнили форму';
	protected $replyFrom = '';
	protected $replyMailable = \TAO\Mail\FormMessageReplyMail::class;
	protected $replyHtmlTemplate = 'email.form.html.reply';
	protected $replyTextTemplate = 'email.form.plain.reply';


	protected function getNotifyList()
	{
		return Arr::wrap($this->notifyEmail);
	}

	protected function getNotifySubject()
	{
		return $this->notifySubject;
	}

	protected function getNotifyFrom()
	{
		return $this->notifyFrom;
	}

	protected function getReplyList()
	{
		return Arr::wrap($this->replyEmail);
	}

	protected function getReplySubject()
	{
		return $this->replySubject;
	}

	protected function getReplyFrom()
	{
		return $this->replyFrom;
	}

	protected function sendNotificationsAndReplies()
	{
		$this->sendNotifications();
		$this->sendReplies();
	}

	protected function isNotificationRequired()
	{
		return $this->sendNotify && $this->notifyMailable && $this->getNotifyList();
	}

	protected function isReplyRequired()
	{
		return $this->sendReply && $this->replyMailable && $this->getReplyList();
	}

	protected function makeNotificationMail()
	{
		$message = new $this->notifyMailable(
			$this,
			$this->getNotifySubject(),
			$this->notifyHtmlTemplate,
			$this->notifyTextTemplate
		);
		if ($from = $this->getNotifyFrom()) {
			$message->from($from);
		}
		return $message;
	}

	protected function makeReplyMessage()
	{
		$message = new $this->replyMailable(
			$this,
			$this->getReplySubject(),
			$this->replyHtmlTemplate,
			$this->replyTextTemplate
		);
		if ($from = $this->getReplyFrom()) {
			$message->from($from);
		}
		return $message;
	}

	protected function sendNotifications()
	{
		if ($this->isNotificationRequired()) {
			$message = $this->makeNotificationMail();
			foreach ($this->getNotifyList() as $contact) {
				\Mail::to($contact)->send(clone $message);
			}
		}
	}

	protected function sendReplies()
	{
		if ($this->isReplyRequired()) {
			$message = $this->makeReplyMessage();
			foreach ($this->getReplyList() as $contact) {
				\Mail::to($contact)->send(clone $message);
			}
		}
	}
}
