<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class IFrame extends Field
{
	public function checkSchema(Blueprint $table)
	{
		return $this;
	}

	public function buildUrl()
	{
		$url = $this->param('url', '/');
		$url = str_replace('{id}', $this->item->getKey(), $url);
		return $url;
	}
	
	public function set($value)
	{
	}
}
