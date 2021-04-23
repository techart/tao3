<?php

namespace TAO\Fields\Type;

use TAO\ORM\Model\Tag;

/**
 * Class MultilinkTags
 * @package TAO\Fields\Type
 *
 * Tag model must implement methods
 * @uses \TAO\ORM\Model\Tag::findTag()
 * @uses \TAO\ORM\Model\Tag::initTagByValue()
 * @uses \TAO\ORM\Model\Tag::setTitle()
 */
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
		$delimiter = (string)$this->param('delimiter') ?: ', ';
		return implode($delimiter, $tags);
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
			$delimiter = (string)$this->param('delimiter') ?: ', ';
			$delimiter = trim($delimiter, ' ');
			foreach (explode($delimiter, $src) as $tag) {
				$tag = trim($tag);
				if (!empty($tag)) {
					$tags[$tag] = $tag;
				}
			}

			$this->setTags($tags);
		}
	}

	/**
	 * Attach Tags to related model
	 *
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
				$tagItem = $this->createNewTag($tag);
			}
			$this->belongsToMany()->attach($tagItem);
		}
	}

	/**
	 * Create new Tag
	 *
	 * @param string $tag title/value of new tag
	 * @return Tag new instance
	 * @throws \TAO\Exception\UnknownDatatype
	 * @throws \TAO\ORM\Exception\NonStrorableObjectSaving
	 */
	private function createNewTag($tag)
	{
		/** @var Tag $newTagItem */
		$newTagItem = $this->relatedModel()->newInstance();

		if (method_exists($newTagItem, 'initTagByValue')) {
			$newTagItem->initTagByValue($tag);
		} else {
			$newTagItem->setTitle($tag);
		}

		$newTagItem->save();

		return $newTagItem;
	}

	protected function itemsFromModel()
	{
		return $this->relatedModel()->itemsForSelectFlat();
	}
}
