<?php

namespace TAO\ORM\Model;

use TAO\ORM\Model as AbstractModel;

class Role extends AbstractModel
{
	protected $table = 'roles';

	public function fields()
	{
		return array(
			'code' => array(
				'type' => 'string(50) index',
				'label' => 'Символьный код',
				'in_list' => true,
				'in_form' => true,
				'style' => 'width: 250px',
			),
			'title' => array(
				'type' => 'string(150)',
				'label' => 'Наименование',
				'in_list' => true,
				'in_form' => true,
				'style' => 'width: 90%',
			),
			'description' => array(
				'type' => 'text',
				'label' => 'Описание',
				'in_list' => false,
				'in_form' => true,
			),

		);
	}

	public function accessEdit($user = false)
	{
		if (!$user) {
			$user = \Auth::user();
		}
		return $user['is_admin'];
	}

	public function findByCode($code)
	{
		foreach ($this->where('code', $code)->get() as $item) {
			return $item;
		}
	}

	public function title()
	{
		return $this['title'];
	}

	public function adminMenuSection()
	{
		return false;
	}

	public function adminTitleList()
	{
		return 'Роли';
	}

	public function adminTitleEdit()
	{
		return 'Редактирование роли';
	}

	public function adminTitleAdd()
	{
		return 'Создание новой роли';
	}

	public function adminAddButtonText()
	{
		return 'Создать';
	}
}
