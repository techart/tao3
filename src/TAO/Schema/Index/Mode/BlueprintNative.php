<?php

namespace TAO\Schema\Index\Mode;

use Doctrine\DBAL\Schema\Index;
use Illuminate\Database\Schema\Blueprint;

abstract class BlueprintNative implements ModeInterface
{
	public function create(Index $index, Blueprint $table)
	{
		$command = $this->blueprintCreateCommand();
		$table->$command($index->getColumns(), $index->getName());
	}

	public function drop(Index $index, Blueprint $table)
	{
		$command = $this->blueprintDeleteCommand();
		$table->$command($index->getName());
	}

	abstract function blueprintCreateCommand(): string;

	abstract function blueprintDeleteCommand(): string;
}