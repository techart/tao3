<?php

namespace TAO\ORM\Model;

use TAO\ORM\Abstracts\FormMessageModel;

class VacancyForm extends FormMessageModel
{
	/**
	 * @return array|mixed
	 */
	public function fields()
	{
		return [
			'fio' => [
				'type' => 'string',
				'label' => 'ФИО',
				'in_list' => true,
				'in_form' => true,
				'group' => 'common',
			],
			'email' => [
				'type' => 'string',
				'label' => 'Почтовый ящик',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			],
			'phone' => [
				'type' => 'string',
				'label' => 'Телефон',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			],
			'vacancy' => [
				'type' => 'string',
				'label' => 'Вакансия',
				'in_form' => true,
				'in_list' => true,
				'group' => 'common',
				'type_in_form' => 'hidden',
			],
			'cv' => [
				'type' => 'public_upload',
				'label' => 'Резюме',
				'in_form' => true,
				'in_list' => false,
				'group' => 'common',
			],
		];
	}

	public function canViewInAdmin()
	{
		return true;
	}

	public function typeTitle()
	{
		return 'Заявки на вакансии';
	}

	protected function beforeRenderForm($context, $template)
	{
		if ($context['vacancy']) {
			$this->field('vacancy')->set($context['vacancy']);
		}
	}

	public function ordered()
	{
		return $this->orderBy('_time', 'desc');
	}
}