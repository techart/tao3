<?php

namespace TAO\Admin\Traits;

use TAO\ORM\Model;

trait Forms
{

	/**
	 * @var
	 */
	protected $editItem;

	protected function templateView()
	{
		return 'table.form.view';
	}

	public function viewAction()
	{
		$this->initViews();

		if (is_null($this->id)) {
			return \TAO::pageNotFound();
		}

		/** @var Model $item */
		$item = $this->datatype()->find($this->id);
		if (!$item || !$item->accessView(\Auth::user())) {
			return \TAO::pageNotFound();
		}

		$this->editItem = $item;
		$fields = $item->adminFormFields();
		foreach(array_keys($fields) as $name) {
			$value = trim($item->field($name)->renderForAdminView());
			if ($value == '') {
				unset($fields[$name]);
			}
		}

		return $this->render($this->templateView(), $this->formViewParams(array(
			'id' => $this->id,
			'item' => $item,
			'title' => $item->title(),
			'fields' => $fields,
		)));
	}


	protected function templateEdit()
	{
		return 'table.form.edit';
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 * @throws \TAO\ORM\Exception\NonStrorableObjectSaving
	 */
	public function editAction()
	{
		$this->initViews();

		if (is_null($this->id)) {
			return \TAO::pageNotFound();
		}

		/** @var Model $item */
		$item = $this->datatype()->find($this->id);
		if (!$item || !$item->accessEdit(\Auth::user())) {
			return \TAO::pageNotFound();
		}

		$this->editItem = $item;
		$fields = $item->adminFormFields();

		$errors = [];
		$request = \Request::getFacadeRoot();
		if ($request->method() == 'POST') {
			foreach ($fields as $field) {
				$field->setFromRequest($request);
			}
			$item->validateForAdmin();
			$errors = $item->errors();
			if (count($errors) == 0) {
				$item->save();
				foreach ($fields as $field) {
					$field->setFromRequestAfterSave($request);
				}
				if ($request->has('_submit_and_stay')) {
					return redirect($this->actionUrl('edit'));
				}
				return redirect($this->actionUrl('list'));
			}
		}
		return $this->render($this->templateEdit(), $this->formViewParams(array(
			'id' => $this->id,
			'item' => $item,
			'title' => $this->titleEdit(),
			'fields' => $fields,
			'action_url' => $this->actionUrl('edit'),
			'submit_text' => $item->adminEditSubmitText(),
			'submit_and_stay_text' => $item->adminEditSubmitAndStayText(),
			'errors' => $errors,
		)));
	}

	protected function templateAdd()
	{
		return 'table.form.add';
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function addAction()
	{
		$this->initViews();

		$item = $this->datatype()->newInstance();
		if (!$item || !$item->accessAdd(\Auth::user())) {
			return \TAO::pageNotFound();
		}
		$this->editItem = $item;
		$fields = $item->adminFormFields();

		$errors = array();

		$request = \Request::getFacadeRoot();
		if ($request->method() == 'POST') {
			foreach ($fields as $field) {
				$field->setFromRequest($request);
			}
			$item->validateForAdmin();
			$errors = $item->errors();
			if (!is_array($errors)) {
				$errors = array();
			}
			if (count($errors) == 0) {
				$item->save();
				$this->id = $item->getKey();
				foreach ($fields as $field) {
					$field->setFromRequestAfterSave($request);
				}
				if ($request->has('_submit_and_stay')) {
					return redirect($this->actionUrl('edit'));
				}
				return redirect($this->actionUrl('list', array('page' => 1)));
			}
		}

		return $this->render($this->templateAdd(), $this->formViewParams(array(
			'id' => null,
			'title' => $this->titleAdd(),
			'fields' => $fields,
			'action_url' => $this->actionUrl('add'),
			'submit_text' => $item->adminAddSubmitText(),
			'submit_and_stay_text' => $item->adminAddSubmitAndStayText(),
			'errors' => $errors,
		)));
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function deleteAction()
	{
		if (is_null($this->id)) {
			return \TAO::pageNotFound();
		}

		$item = $this->datatype()->find($this->id);
		if (!$item || !$item->accessEdit(\Auth::user())) {
			return \TAO::pageNotFound();
		}
		$item->delete();
		return redirect($this->actionUrl('list'));
	}

	/**
	 * @param $params
	 * @return array
	 */
	protected function formViewParams($params)
	{
		$firstTab = false;
		$tabs = $this->datatype()->adminFormTabs();
		$etabs = $tabs;
		foreach ($params['fields'] as $field) {
			$etabs[$field->adminTab()] = true;
		}
		foreach ($etabs as $tab => $v) {
			if ($v !== true && isset($tabs[$tab])) {
				unset($tabs[$tab]);
			}
		}

		if (is_array($tabs)) {
			foreach (array_keys($tabs) as $tab) {
				$firstTab = $tab;
				break;
			}
		}

		return array_merge(array(
			'item' => $this->editItem,
			'datatype' => $this->datatype(),
			'list_url' => $this->actionUrl('list'),
			'tabs' => $tabs,
			'first_tab' => $firstTab
		), $params);
	}

	protected function titleEdit()
	{
		$item = empty($this->editItem) ? $this->datatype() : $this->editItem;
		return $item->adminTitleEdit();
	}

	protected function titleAdd()
	{
		return $this->datatype()->adminTitleAdd();
	}

	protected function canAdd()
	{
		return $this->datatype()->accessAdd(\Auth::user());
	}

}