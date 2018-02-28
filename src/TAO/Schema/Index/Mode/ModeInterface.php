<?php

namespace TAO\Schema\Index\Mode;

use Doctrine\DBAL\Schema\Index;
use Illuminate\Database\Schema\Blueprint;

/**
 * Interface ModeInterface
 * @package TAO\Schema\Index\Mode
 *
 * Интерфейс для объектов, реализующих логику работы с различными видами индексов
 */
interface ModeInterface
{
	public function create(Index $index, Blueprint $table);

	public function drop(Index $index, Blueprint $table);
}