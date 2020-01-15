<?php

namespace TAO\ORM\Traits;

/**
 * Trait CheckUniqueUrl
 *
 * Трейт для проверки уникальности урлов
 *
 */
trait CheckUniqueUrl
{
	/**
	 * @return bool
	 *
	 * Проверка урла на уникальность среди элементов текущего дататипа
	 *
	 */
	public function checkUniqueUrl()
	{
		$itemWithSuchUrl = $this->getAccessibleItems()
			->where('url', $this->url)
			->whereKeyNot($this->getKey())
			->first();

		if ($this->isactive && $itemWithSuchUrl) {
			return 'Запись с таким адресом уже существует [id=' . $itemWithSuchUrl->getKey() . ']';
		}

		return true;
	}

}
