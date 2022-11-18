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
		$mail = MessageConverter::toEmail($message->getOriginalMessage());

		$headers = $mail->getPreparedHeaders();

		$subject = $headers->get('subject');
		$subject = trim(preg_replace('{^Subject:}i', '', trim($subject->toString())));

		$emails = $this->getTo($mail);
		$headers->remove('to');
		$headers->remove('cc');
		$headers->remove('bcc');

		$head = $headers->toString();

		$_part = $mail->getBody();
		if (($_part_headers = $_part->getPreparedHeaders()) &&
			($_part_head = $_part_headers->toString())) {
			$head .= $_part_head;
		}
		$body = $_part->bodyToString();

		foreach ($emails as $email) {
			mail($email, $subject, $body, $head);
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
