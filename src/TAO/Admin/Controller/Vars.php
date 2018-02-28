<?php

namespace TAO\Admin\Controller;

use TAO\Admin\Traits\Actions;

class Vars extends Base
{
	use Actions;

	protected $groups = ['-' => ['title' => false]];
	protected $vars = [];
	protected $config = [];
	protected $title;
	protected $editItem;

	protected function listAction()
	{
		return $this->render('vars ~ index');
	}

	protected function editAction()
	{
		if (is_null($this->id)) {
			return \TAO::pageNotFound();
		}

		$item = \TAO::vars($this->id);
		if (!$item || !$item->accessEdit(\Auth::user())) {
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
			$errors = $item->errors();
			if (!is_array($errors)) {
				$errors = array();
			}
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

		return $this->render('vars ~ edit', array(
			'id' => $this->id,
			'item' => $item,
			'title' => $item->titleEdit(),
			'fields' => $fields,
			'tabs' => false,
			'action_url' => $this->actionUrl('edit'),
			'submit_text' => 'Сохранить',
			'submit_and_stay_text' => 'Сохранить и остаться',
			'errors' => $errors,
		));
	}

	public function setupContextForRender($context)
	{
		$context['varGroups'] = $this->groups;
		if (!isset($context['title'])) {
			$context['title'] = $this->title;
		}
		$context['controller'] = $this;
		return $context;
	}

	public function beforeAction($method, $parameters)
	{
		parent::beforeAction($method, $parameters);
		$this->cfg = $cfg = app()->tao->router('admin')->vars;

		$groups = isset($cfg['groups']) ? $cfg['groups'] : [];
		foreach ($groups as $code => $title) {
			$this->groups[$code] = [
				'title' => $title,
				'vars' => [],
			];
		}

		$vars = isset($cfg['vars']) ? $cfg['vars'] : [];
		foreach ($vars as $code => $data) {
			$data = \TAO::vars()->canonizeVarParams($data);
			$group = isset($data['group']) ? $data['group'] : '-';
			$group = isset($this->groups[$group]) ? $group : '-';
			$this->groups[$group]['vars'][$code] = $data;
		}

		foreach ($this->groups as $code => $data) {
			if (!isset($data['vars']) || count($data['vars']) == 0) {
				unset($this->groups[$code]);
			}
		}

		$this->title = isset($cfg['title']) ? $cfg['title'] : 'Настройки';
	}

}