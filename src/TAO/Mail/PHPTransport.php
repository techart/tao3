<?php

namespace TAO\Mail;

use Illuminate\Mail\Transport\Transport as BaseTransport;

class PHPTransport extends BaseTransport
{
	public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
	{
		$body = $message->toString();
		$bodyPos = strpos($body, "\r\n\r\n");
		$body = trim(substr($body, $bodyPos));
		$headers = $message->getHeaders();
		$subject = $headers->get('subject');
		
		foreach($this->getTo($message) as $email) {
			$rc = mail($email, trim($subject->toString()), $body, trim($headers->toString()));
		}
		return $rc;
	}
	
	protected function formatEmail($email, $name = null)
	{
		if (is_array($email)) {
			$name = current($email);
			$email = key($email);
		}
		if (!empty($name)) {
			return '=?utf-8?B?='.base64_encode($name)." <$email>";
		}
		return $email;
	}
	
	protected function getTo($message)
	{
		$emails = array_merge((array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc());
		$out = [];
		foreach ($emails as $address => $name) {
			$out[] = $this->formatEmail($address, $name);
		}
		return $out;
	}
}