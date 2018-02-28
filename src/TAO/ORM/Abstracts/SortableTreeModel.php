<?php

namespace TAO\ORM\Abstracts;

use Illuminate\Database\Eloquent\Builder;

class SortableTreeModel extends TreeModel
{
	use \TAO\ORM\Traits\Sortable,
		\TAO\ORM\Traits\Tree,
		\TAO\ORM\Traits\Title;

	protected function initExtraFields()
	{
		$this->initExtra('Sortable', 'Tree', 'Title');
		$this->extraFields['title']['in_list'] = false;
	}

	/**
	 * @return Builder
	 */
	public function ordered()
	{
		return $this->orderBy('weight')->orderBy('id');
	}

	public function beforeSave()
	{
		parent::beforeSave();
		if ((int)$this['weight'] == 0) {
			$this['weight'] = (int)$this->max('weight') + 1;
		}
	}

}