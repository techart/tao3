<?php

namespace TAO\Fields\Type\Gallery;

class Entry extends \TAO\Fields\Type\Attaches\Entry
{
	protected $image = false;
	
	public function imageObject()
	{
		if (!$this->image) {
			$this->image = \Image::make(\Storage::get($this->data['path']));
		}
		return $this->image;
	}
	
	public function getSize()
	{
		return $this->imageObject()->getSize();
	}
	
	public function width()
	{
		return isset($this->data['width'])? $this->data['width'] : $this->getSize()->width;
	}
	
	public function height()
	{
		return isset($this->data['height'])? $this->data['height'] : $this->getSize()->height;
	}
}