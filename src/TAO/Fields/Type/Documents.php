<?php

namespace TAO\Fields\Type;

class Documents extends Attaches
{
	/**
	 * @return string
	 */
	public function inputTemplateFrom()
	{
		return 'attaches';
	}

	/**
	 * @return string
	 */
	public function templateEntryJS()
	{
		return 'js-entry-documents';
	}

	/**
	 * @return mixed
	 */
	public function infoFieldsSrc()
	{
		return \TAO::merge([
			'text title' => 'Название',
			'date date' => 'Дата',
		], $this->param('info', []));
	}

	/**
	 * @return null|bool
	 */
	public function isSortable()
	{
		return $this->param('sortable', true);
	}
}
