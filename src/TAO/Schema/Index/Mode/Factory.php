<?php

namespace TAO\Schema\Index\Mode;

use Doctrine\DBAL\Schema\Index;

/**
 * Class Factory
 * @package TAO\Schema\Index\Mode
 *
 * Фабрика для создания объектов для управления различными видами индексов
 */
class Factory
{
	/**
	 * @param Index $index
	 * @return ModeInterface
	 */
	public static function mode(Index $index)
	{
		if ($index->isUnique()) {
			$class = 'Unique';
		} else if ($index->hasFlag('fulltext')) {
			$class = 'Fulltext';
		} else {
			$class = 'Simple';
		}
		return app()->make('TAO\\Schema\\Index\\Mode\\' . $class);
	}
}