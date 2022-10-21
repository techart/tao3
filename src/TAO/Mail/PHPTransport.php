<?php

namespace TAO\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PHPTransport extends AbstractTransport
{

	/**
	 * {@inheritDoc}
	 */
	protected function doSend(SentMessage $message): void
	{
		$email = MessageConverter::toEmail($message->getOriginalMessage());
		$body = $email->getHtmlBody();
		$bodyPos = mb_strpos($body, "\r\n\r\n");
		$body = trim(mb_substr($body, $bodyPos));
		$headers = $email->getHeaders();
		$subject = $headers->get('subject');
		$emails = $this->getTo($email);
		$headers->remove('to');
		$headers->remove('cc');
		$headers->remove('bcc');
		$headers->addHeader('content-type', 'text/html');
		$headers->addHeader('charset', $email->getHtmlCharset());
		$subject = trim(preg_replace('{^Subject:}i', '', trim($subject->toString())));

		foreach($emails as $email) {
			mail($email, $subject, $body, trim($headers->toString()));
		}
	}


	protected function formatEmail(string $email, $name = null): string
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


	protected function getTo(\Symfony\Component\Mime\Email $message): array
	{
		$emails = array_merge((array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc());
		$out = [];
		foreach ($emails as $name) {
			$out[] = $this->formatEmail($name->toString());
		}
		return $out;
	}

	/**
	 * Get the string representation of the transport.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return 'php';
	}
}
