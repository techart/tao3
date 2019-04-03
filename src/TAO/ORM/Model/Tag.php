<?php

namespace TAO\ORM\Model;

use Illuminate\Database\Eloquent\Builder;
use TAO\ORM\Model as AbstractModel;

abstract class Tag extends AbstractModel
{
	public $adminMenuSection = 'Словари';

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

	public function findTag($name)
	{
		foreach ($this->where('title', $name)->get() as $item) {
			return $item;
		}
	}

	public function initTagByValue($value)
	{
		$this->setTitle($value);
	}

	/**
	 * @return Builder
	 */
	public function ordered()
	{
		return $this->orderBy('title');
	}

	public function title()
	{
		return preg_replace('/\s+/', ' ', trim($this['title']));
	}

	public function setTitle($value)
	{
		$this['title'] = $value;
	}
}
