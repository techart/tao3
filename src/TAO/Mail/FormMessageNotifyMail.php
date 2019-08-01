<?php

namespace TAO\Mail;

use TAO\Fields\Field;
use TAO\Mail\Abstracts\FormMessageMail;
use TAO\ORM\Abstracts\FormMessageModel;

class FormMessageNotifyMail extends FormMessageMail
{
	/**
	 * @param FormMessageModel $form
	 * @return Field[]
	 * @throws \TAO\Fields\Exception\UndefinedField
	 */
	protected function getFields($form)
	{
		return array_where($form->fieldsObjects(), function ($field) {
			/** @var Field $field */
			return $field->param('in_notify', true);
		});
	}
}
