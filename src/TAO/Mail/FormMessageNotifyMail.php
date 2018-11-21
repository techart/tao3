<?php

namespace TAO\Mail;

use TAO\Mail\Abstracts\FormMessageMail;

class FormMessageNotifyMail extends FormMessageMail
{
	protected function getFields($form)
	{
		return $form->fieldsObjects();
	}
}
