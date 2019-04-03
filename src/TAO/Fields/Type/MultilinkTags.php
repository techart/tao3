<?php

namespace TAO\Fields\Type;

use TAO\ORM\Model\Tag;

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

	/**
	 * @return string
	 * @throws \TAO\Exception\UnknownDatatype
	 */
	public function inputValue()
	{
		$tags = [];
		foreach ($this->relatedItems() as $item) {
			$tag = $item->title();
			$tags[$tag] = $tag;
		}
		sort($tags);
		return implode(', ', $tags);
	}

	protected function getValueFromRequest($request)
	{
		return $request->input($this->name);
	}

	/**
	 * @param \Request $request
	 * @throws \TAO\Exception\UnknownDatatype
	 * @throws \TAO\ORM\Exception\NonStrorableObjectSaving
	 */
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

			$this->setTags($tags);
		}
	}

	/**
	 * @param array $tags
	 * @throws \TAO\Exception\UnknownDatatype
	 * @throws \TAO\ORM\Exception\NonStrorableObjectSaving
	 */
	public function setTags($tags)
	{
		$this->belongsToMany()->detach();
		foreach ($tags as $tag) {
			/** @var Tag $tagModel */
			$tagModel = $this->relatedModel();
			$tagItem = $tagModel->findTag($tag);
			if (!$tagItem) {
				/** @var Tag $tagItem */
				$tagItem = $this->relatedModel()->newInstance();
				if (method_exists($tagItem, 'initTagByValue')) {
					$tagItem->initTagByValue($tag);
				} else {
					$tagItem->setTitle($tag);
				}
				$tagItem->save();
			}
			$this->belongsToMany()->attach($tagItem);
		}
	}

	protected function itemsFromModel()
	{
		return $this->relatedModel()->itemsForSelectFlat();
	}


}
