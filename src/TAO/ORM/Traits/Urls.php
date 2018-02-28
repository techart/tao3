<?php

namespace TAO\ORM\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Urls
{
	public function automaticRoutes()
	{
		return false;
	}

	public function routeSelector($data = [])
	{
		$this->selector()->route($data);
		return $this;
	}

	public function navigationItem()
	{
		return [
			'url' => $this->url(),
			'title' => $this->title(),
		];
	}

	public function navigation()
	{
		$args = func_get_args();
		$struct = [];
		foreach ($this->getAccessibleItems()->get() as $item) {
			$item = $item->navigationItem($args);
			if (is_array($item)) {
				$struct[] = $item;
			}
		}
		return $struct;
	}
}