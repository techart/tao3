<?php

namespace TAO\ORM\Model;

use TAO\ORM\Abstracts\PageModel;

class Vacancy extends PageModel
{
	public function fields()
	{
		return [
			'activity' => [
				'type' => 'checkbox',
				'label' => 'Активность',
				'in_list' => true,
				'in_form' => true,
				'group' => 'common',
			],
			'salary' => [
				'type' => 'string',
				'label' => 'Зарплата',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			],
			'requirements' => [
				'type' => 'text',
				'label' => 'Требования',
				'style' => 'width:90%;height:200px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'duties_requir',
			],
			'duties' => [
				'type' => 'text',
				'label' => 'Обязанности',
				'style' => 'width:90%;height:200px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'duties_requir',
			],
			'description' => [
				'type' => 'text',
				'label' => 'Описание',
				'style' => 'width:90%;height:200px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'content',
			],
			'city' => [
				'type' => 'string',
				'label' => 'Город',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			],
		];
	}

	public function adminFormGroups()
	{
		return [
			'common' => 'Основные параметры',
			'common.meta' => 'SEO-информация',
			'content' => 'Контент',
			'duties_requir' => 'Требования и обязанности'
		];
	}

	public function adminMenuSection()
	{
		return 'Материалы';
	}

	public function typeTitle()
	{
		return 'Вакансии';
	}

	public function adminTitleEdit()
	{
		return 'Редактирование вакансии';
	}

	public function adminTitleAdd()
	{
		return 'Создание вакансии';
	}

	public function adminAddButtonText()
	{
		return 'Создать вакансию';
	}

	public function convertFieldToList($fieldName, $id)
	{
		$r = $this->select($fieldName)->where('id',$id)->first();
		return explode("\n", trim($r->field($fieldName)));
	}
}