<?php

namespace TAO\ORM\Traits;

trait Metas
{
	public function initExtraMetas()
	{
		$this->extraFields = \TAO::merge($this->extraFields, [
			'meta_title' => array(
				'type' => 'string(250)',
				'label' => 'Title',
				'style' => 'width:90%;',
				'weight' => 900100,
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.meta',
			),
			'meta_description' => array(
				'type' => 'text',
				'label' => 'Description',
				'weight' => 900200,
				'in_list' => false,
				'in_form' => true,
				'style' => 'width: 90%; height:100px;',
				'group' => 'common.meta',
			),
			'meta_keywords' => array(
				'type' => 'text',
				'label' => 'Keywords',
				'weight' => 900300,
				'in_list' => false,
				'in_form' => true,
				'style' => 'width: 90%; height:100px;',
				'group' => 'common.meta',
			),
		]);
	}

	/**
	 * Возвращает true, если в указанном режиме отображения модели нужно установить ее meta-теги
	 *
	 * @param $renderMode
	 * @return bool
	 */
	protected function isPageMetasSettingRequired($renderMode)
	{
		return $renderMode == 'full';
	}

	/**
	 * Устанавливает меты модели на текущей страницы
	 */
	public function setPageMetas()
	{
		\TAO::setMetas($this->getPageMetas());
	}

	/**
	 * Формирует список мет текущей модели. Игнорирует меты с пустым значением.
	 *
	 * @return array
	 */
	public function getPageMetas()
	{
		return array_filter([
			'title' => $this->getMetaTitle(),
			'description' => $this->getMetaDescription(),
			'keywords' => $this->getMetaKeywords()
		]);
	}

	public function getMetaTitle()
	{
		return $this->getMeta('title', $this->title());
	}

	public function getMetaDescription()
	{
		return $this->getMeta('description');
	}

	public function getMetaKeywords()
	{
		return $this->getMeta('keywords');
	}

	/**
	 * Возвращает значение меты $name модели. Ищет значение в поле meta_{metaname}, если значение отсутсвует, то
	 * возвращает $defaultValue.
	 *
	 * @param $name
	 * @param string $defaultValue
	 * @return string
	 */
	public function getMeta($name, $defaultValue = '')
	{
		$meta = $defaultValue;
		$field = $this->field('meta_' . $name);
		if ($field->isNotEmpty()) {
			$meta = $field->value();
		}
		return $meta;
	}
}