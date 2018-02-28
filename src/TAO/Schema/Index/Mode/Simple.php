<?php

namespace TAO\Schema\Index\Mode;

class Simple extends BlueprintNative
{
	function blueprintCreateCommand(): string
	{
		return 'index';
	}

	function blueprintDeleteCommand(): string
	{
		return 'dropIndex';
	}
}