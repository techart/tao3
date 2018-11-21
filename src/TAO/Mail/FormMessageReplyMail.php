<?php

namespace TAO\Mail;

use TAO\Mail\Abstracts\FormMessageMail;

class FormMessageReplyMail extends FormMessageMail
{
	protected function getFields($form)
	{
		return array_where($form->fieldsObjects(), function ($field, $key) {
			return isset($field->data['in_reply']) && $field->data['in_reply'];
		});
	}

}
