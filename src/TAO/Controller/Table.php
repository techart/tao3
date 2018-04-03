<?php

namespace TAO\Controller;

use TAO\Admin\Traits\Actions;
use TAO\Admin\Traits\Forms;
use TAO\Admin\Traits\Table as TableTrait;

class Table extends \TAO\Controller
{
	use Actions, TableTrait, Forms;

	protected $datatype;
	protected $datatypeCode;

	public function index($code = false)
	{
		if ($code) {
			$this->datatypeCode = $code;
			$this->datatype = \TAO::datatype($code);
		}
		return $this->entryPointAction();
	}

	protected function datatype()
	{
		return $this->datatype;
	}

	protected function initViews()
	{
		$code = $this->datatype()->getDatatype();
		\TAO::addViewsPrefix("datatype.{$code}.");
	}

	protected function templateTable()
	{
		return 'table.list.table-public';
	}

	protected function templateTree()
	{
		return 'table.list.tree-public';
	}

	protected function templateEdit()
	{
		return 'table.form.edit-public';
	}

	protected function templateAdd()
	{
		return 'table.form.add-public';
	}

	protected function render($template, $context = array())
	{
		$context['with_filter'] = false; // Фильтры на публичной части пока не поддерживаются
		return parent::render($template, $context);
	}
}