<?php

namespace TAO\Admin\Traits;

trait Actions
{
	/**
	 * @var
	 */
	protected $action;
	/**
	 * @var
	 */
	protected $filter;
	/**
	 * @var
	 */
	protected $order;
	/**
	 * @var
	 */
	protected $page;
	/**
	 * @var
	 */
	protected $id;

	/**
	 * @return mixed
	 */
	public function entryPointAction()
	{
		$this->action = \Request::get('action', 'list');
		$this->filter = \Request::get('filter', array());
		$this->order = \Request::get('order', array());
		$this->page = \Request::get('page', 1);
		$this->id = \Request::get('id', null);
		$method = "{$this->action}Action";

		return $this->$method();
	}

	/**
	 * @param $action
	 * @param array $params
	 * @return string
	 */
	public function actionUrl($action, $params = array())
	{
		$uri = \Request::getPathInfo();
		$data = array(
			'order' => $this->order,
			'filter' => $this->filter,
		);
		if ($action != 'list') {
			$data['action'] = $action;
			if ($this->id >= 1) {
				$data['id'] = $this->id;
			}
		}
		if ($this->page > 1) {
			$data['page'] = $this->page;
		}
		if (isset($params['__no_filter'])) {
			unset($data['filter']);
			unset($params['__no_filter']);
		}
		if (isset($params['__no_page'])) {
			unset($data['page']);
			unset($params['__no_page']);
		}
		$data = array_merge($data, $params);
		if (count($data) > 1) {
			$q = trim(http_build_query($data));
			if ($q != '') {
				$uri .= '?' . $q;
			}
		}
		return $uri;
	}
}