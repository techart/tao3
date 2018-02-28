<?php

namespace TAO\Frontend;

class Repository extends \Techart\Frontend\Templates\Repository
{
	protected function createDefaultRenders()
	{
		parent::createDefaultRenders();
		$this->add('default', Renderer::class);
	}

}