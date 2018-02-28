<?php

namespace TAO\ORM\Model;

class Categories extends \TAO\ORM\Abstracts\SortableTreeModel
{
	use \TAO\ORM\Traits\Switchable,
		\TAO\ORM\Traits\Addressable,
		\TAO\ORM\Traits\Metas;

	protected function initExtraFields()
	{
		$this->initExtra('Sortable', 'Switchable', 'Addressable', 'Tree', 'Title', 'Metas');
		$this->extraFields['title']['in_list'] = false;
	}

	public function adminFormGroups()
	{
		return array(
			'common' => 'Основные параметры',
			'common.meta' => 'SEO-информация',
		);
	}
}