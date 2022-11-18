<?php

namespace TAO\Mail\Abstracts;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class FormMessageMail extends Mailable
{
	use Queueable, SerializesModels;

	protected $data = [];
	protected $files = [];
	protected $name = '';
	protected $mailSubject;
	protected $htmlTemplate = false;
	protected $textTemplate = false;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($form, $subject, $htmlTemplate = false, $textTemplate = false)
	{
		$this->prepareData($form);
		$this->name = $form->getFormTitle();
		$this->mailSubject = $subject;
		if ($htmlTemplate) {
			$this->htmlTemplate = $htmlTemplate;
		}
		if ($textTemplate) {
			$this->textTemplate = $textTemplate;
		}
	}

	/**
	 * Build the message.
	 *
	 * @return bool|$this
	 */
	public function build()
	{
		if (!$this->htmlTemplate && !$this->textTemplate) {
			return false;
		}

		$this->subject($this->mailSubject);
		if ($this->htmlTemplate) {
			$this->markdown($this->htmlTemplate);
		}
		if ($this->textTemplate) {
			$this->text($this->textTemplate);
		}
		$this->with([
			'name' => $this->name,
			'data' => $this->data,
		]);
		$this->attachFiles();
		return $this;
	}


	protected function getFields($form)
	{
		return array();
	}


	protected function prepareData($form)
	{
		foreach ($this->getFields($form) as $field) {
			if ('public_upload' === $field->param('type')) {
				$this->files[] = $field;
			} else {
				$name = $field->param('label');
				$value = $field->render();
				$this->data[] = [
					'name' => $name,
					'value' => $value,
				];
			}
		}
	}


	protected function attachFiles()
	{
		foreach ($this->files as $field) {
			if ($field->isNotEmpty()) {
				$this->attach(\Storage::path($field->value()));
			}
			break;
		}
	}
}
