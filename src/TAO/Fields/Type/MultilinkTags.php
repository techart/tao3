<?php

namespace TAO\Fields\Type;

use TAO\Fields\Exception\InvalidTypeOptions;

class MultilinkTags extends Multilink
{
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		if (!$style) {
			$style = 'width:90%;height:50px;';
		}
		return $style;
	}

	public function inputValue()
	{
		$value = '';
		$tags = [];
		foreach ($this->relatedItems() as $item) {
			$tag = $item->title();
			$tags[$tag] = $tag;
		}
		sort($tags);
		$value = implode(', ', $tags);
		return $value;
	}

	public function setFromRequestAfterSave($request)
	{
		if ($request->has($this->name)) {
			$src = $request->get($this->name);
			$tags = [];
			foreach (explode(',', $src) as $tag) {
				$tag = trim($tag);
				if (!empty($tag)) {
					$tags[$tag] = $tag;
				}
			}
			$this->belongsToMany()->detach();
			foreach ($tags as $tag) {
				$tagItem = $this->relatedModel()->findTag($tag);
				if (!$tagItem) {
					$tagItem = $this->relatedModel()->newInstance();
					$tagItem->setTitle($tag);
					$tagItem->save();
				}
				$this->belongsToMany()->attach($tagItem);
			}
		}
	}


}
