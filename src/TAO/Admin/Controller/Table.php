<?php

namespace TAO\Admin\Controller;

use Illuminate\Support\Facades\Auth;
use TAO\Admin\Traits\Actions;
use TAO\Admin\Traits\Forms;
use TAO\Admin\Traits\Table as TableTrait;

class Table extends Base
{
	use Actions, TableTrait, Forms;

	protected $datatype;
	protected $datatypeCode;

	protected function datatype()
	{
		if (is_null($this->datatype)) {
			$this->datatype = app()->tao->router('admin')->datatype;
			$this->datatypeCode = app()->tao->router('admin')->datatypeCode;
		}
		return $this->datatype;
	}

	protected function accessAction($method, $parameters)
	{
		$acc = parent::accessAction($method, $parameters);
		if ($acc === true) {
			if (!$this->datatype()->accessAdmin(Auth::user())) {
				return false;
			}
		}
		return $acc;
	}

	public function layout()
	{
		if (request()->get('is_embedded', false)) {
			return app('tao.admin')->embeddedLayout();
		}
		return parent::layout();
	}

	protected function initViews()
	{
		$code = $this->datatype()->getDatatype();
		\TAO::addViewsPrefix("datatype.{$code}.");
	}
}