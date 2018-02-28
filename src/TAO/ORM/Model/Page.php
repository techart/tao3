<?php

namespace TAO\ORM\Model;

use TAO\ORM\Abstracts\PageModel;

class Page extends PageModel
{
	protected $table = 'pages';
	protected $idType = 'uuid';

	public function fields()
	{
		return array(
			'content' => array(
				'type' => 'text',
				'label' => 'Текст страницы',
				'style' => 'width:90%;height:400px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'content',
			),
		);
	}

	public function automaticRoutes()
	{
		$this->routePageByUrl();
	}

	public function adminMenuSection()
	{
		return 'Материалы';
	}

	public function adminTitle()
	{
		return 'Страницы';
	}

	public function adminTitleEdit()
	{
		return 'Редактирование страницы';
	}

	public function adminTitleAdd()
	{
		return 'Создание новой страницы';
	}

	public function adminAddButtonText()
	{
		return 'Создать страницу';
	}
}
