<?php

namespace TAO\Admin\Traits;

use Illuminate\Support\Facades\Auth;
use TAO\Fields;
use TAO\Request;

trait Table
{
	/**
	 * @var bool
	 */
	protected $canAdd = false;
	/**
	 * @var bool
	 */
	protected $canEdit = false;
	/**
	 * @var bool
	 */
	protected $canDelete = false;
	/**
	 * @var bool
	 */
	protected $canView = false;
	/**
	 * @var bool
	 */
	protected $canCopy = false;

	protected $canExport = false;

	protected $canCsv = true;

	/**
	 * @var Fields\Field[]
	 */
	protected $filterFields = [];

	/**
	 * @var bool
	 */
	protected $isFilterInitialized = false;


	/**
	 * @return Fields\Field[]
	 */
	protected function filterFields()
	{
		if (!$this->isFilterInitialized) {
			$this->initializeFilter();
		}
		return $this->filterFields;
	}

	protected function filterFormFields()
	{
		$out = [];
		foreach ($this->filterFields() as $name => $field) {
			if ($field->param('in_filter_form', true)) {
				$out[$name] = $field;
			}
		}
		return $out;
	}

	protected function filterValues()
	{
		if (!$this->isFilterInitialized) {
			$this->initializeFilter();
		}
		return $this->collectFilterValues();
	}

	protected function initializeFilter()
	{
		$this->filterFields = $this->makeFilterFields();
		$this->setFilterValuesFromRequest();
		$this->isFilterInitialized = true;
	}

	protected function filterFieldsData()
	{
		$fieldsData = $this->datatype()->filter();
		if (empty($fieldsData) && !is_array($fieldsData)) {
			$fieldsData = [];
		}
		return $fieldsData;
	}

	protected function makeFilterDummyModel()
	{
		$model = new \TAO\ORM\Dummy\Model();
		$model['id'] = 1;
		return $model;
	}

	protected function makeFilterFields()
	{
		$filterFields = [];
		$model = $this->makeFilterDummyModel();
		$fieldsData = $this->filterFieldsData();
		foreach ($fieldsData as $fieldName => $data) {
			$filterFields[$fieldName] = $this->makeFilterField($fieldName, $data, $model, $filterFields);
		}
		return $filterFields;
	}

	/**
	 * @param Request $request
	 */
	protected function setFilterValuesFromRequest($request = null)
	{
		if (!$request) {
			$request = \Request::getFacadeRoot();
		}
		foreach ($this->filterFields as $field) {
			$field->setFromFilter($request);
		}
	}

	/**
	 * @param $fieldName
	 * @param $data
	 * @param $model
	 *
	 * @return Fields\Field
	 */
	protected function makeFilterField($fieldName, $data, $model)
	{
		$field = app('tao.fields')->create($fieldName, $data, $model, 'filter');
		$field->setupDefault();
		return $field;
	}

	protected function collectFilterValues()
	{
		$values = [];
		foreach ($this->filterFields() as $field) {
			$values[$field->name] = $field->value();
		}
		return $values;
	}

	protected function requestHasFilter($request)
	{
		return $request->has('filter');
	}

	public function filterAction()
	{
		$request = \Request::getFacadeRoot();
		if ($request->method() == 'POST') {
			$fields = $this->filterFields();
			$this->filter = [];
			foreach ($fields as $name => $field) {
				if ($request->has($name)) {
					$this->filter[$name] = $request->get($name);
				}
			}
		}
		return redirect($this->actionUrl('list'));
	}

	protected function additionalActions()
	{
		$actions = [];
		if ($this->canCsv && $this->csvFields() && $this->countRows()>0) {
			$actions['csv'] = [
				'button' => 'btn btn-info',
				'icon' => 'icon-share icon-white fa fa-share',
				'title' => 'Экспорт в CSV',
				'url' => $this->actionUrl('csv'),
			];
		}
		return $actions;
	}
	
	public function csvLabel($field, $param)
	{
		if (is_string($param)) {
			return $param;
		}
		if (ends_with($field, '()')) {
			return $field;
		}
		return $this->datatype()->field($field)->labelInAdminList();
	}
	
	public function csvValue($row, $field, $param)
	{
		if (ends_with($field, '()')) {
			$method = trim(substr($field, 0, strlen($field)-2));
			return $row->$method();
		}
		return $row->field($field)->csvValue();
	}
	
	public function csvRow($row, $fields)
	{
		$values = [];
		foreach($fields as $field => $param) {
			$values[] = $this->csvValue($row, $field, $param);
		}
		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, $values, ';');
		fclose($df);
		return ob_get_clean();
	}

	public function csvAction()
	{
		$rows = $this->filtered()->get();
		$fields = $this->csvFields();
		$csv = '';
		foreach($fields as $field => $param) {
			$csv .= empty($csv)? '' : ';';
			$csv .= $this->csvLabel($field, $param);
		}
		$csv .= "\n";
		foreach($rows as $row) {
			$csv .= $this->csvRow($row, $fields);
		}
		$fileName = $this->csvFileName();
		return response($csv, 200, [
			'Content-Type' => 'text/x-csv',
			'Content-Disposition' => "attachment; filename={$fileName}"
		]);
	}

	protected function csvFileName()
	{
		return $this->datatypeCode . '.csv';
	}

	protected function templateTable()
	{
		return 'table.list.table';
	}

	/**
	 * @return mixed
	 */
	public function listAction()
	{
		$this->initViews();

		if ($this->datatype()->checkIfTree()) {
			return $this->treeAction();
		}

		$filter = $this->filterFormFields();

		$count = $this->countRows();
		$numPages = ceil($count / $this->perPage());
		$rows = $this->prepareRows();
		return $this->render($this->templateTable(), [
			'title' => $this->titleList(),
			'datatype' => $this->datatype(),
			'fields' => $this->listFields(),
			'count' => $count,
			'per_page' => $this->perPage(),
			'numpages' => $numPages,
			'rows' => $rows,
			'can_add' => $this->canAdd(),
			'can_edit' => $this->canEdit,
			'can_delete' => $this->canDelete,
			'can_copy' => $this->canCopy,
			'can_view' => $this->canView,
			'add_text' => $this->datatype()->adminAddButtonText(),
			'filter' => $filter,
			'with_filter' => !empty($filter),
			'filter_url' => $this->actionUrl('filter', ['__no_filter' => true, '__no_page' => true]),
			'reset_filter_url' => $this->actionUrl('list', ['__no_filter' => true, '__no_page' => true]),
			'sidebar_visible' => !empty($this->filter),
			'filter_empty' => empty($this->filter),
			'additional_actions' => $this->additionalActions(),
			//'sidebar_visible' => true,
			'with_row_actions' => ($this->canEdit || $this->canDelete || $this->canCopy || $this->canView),
			'pager_callback' => array($this, 'pageUrl'),
			'page' => $this->page,
			'user' => Auth::user(),
			'order_fields' => $this->orderFields(),
		]);
	}

	protected function orderFields()
	{
		$out = [];
		foreach ($this->datatype()->fieldsObjects() as $name => $field) {
			if ($order = $field->param('order')) {
				$out[$name] = $this->actionUrl('list', array(
					'page' => 1,
					'order' => $order,
				));
			}
		}
		return $out;
	}

	protected function templateTree()
	{
		return 'table.list.tree';
	}

	/**
	 * @return mixed
	 */
	public function treeAction()
	{
		$this->initViews();

		$filter = $this->filter;
		$filter['max_depth'] = $this->datatype()->adminMaxDepth();
		$tree = $this->datatype()->buildTree($filter);
		$this->prepareTree($tree);

		$fields = $this->listFields();
		$fieldsBefore = [];
		$fieldsAfter = [];
		foreach ($fields as $name => $field) {
			if ($field->param('before_tree_title')) {
				$fieldsBefore[$name] = $field;
			} else {
				$fieldsAfter[$name] = $field;
			}
		}

		return $this->render($this->templateTree(), [
			'title' => $this->titleList(),
			'count' => count($tree),
			'datatype' => $this->datatype(),
			'fields' => $fields,
			'fieldsBefore' => $fieldsBefore,
			'fieldsAfter' => $fieldsAfter,
			'tree' => $tree,
			'can_add' => $this->canAdd(),
			'can_edit' => $this->canEdit,
			'can_delete' => $this->canDelete,
			'can_copy' => $this->canCopy,
			'can_view' => $this->canView,
			'add_text' => $this->datatype()->adminAddButtonText(),
			'with_filter' => false,
			'with_row_actions' => ($this->canEdit || $this->canDelete || $this->canCopy || $this->canView),
			'user' => Auth::user()
		]);
	}

	/**
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function weightAction()
	{
		$with = app()->request()->get('with');
		if (is_null($this->id) || is_null($with)) {
			return \TAO::pageNotFound();
		}

		$item1 = $this->datatype()->find($this->id);
		$item2 = $this->datatype()->find($with);
		if (!$item1 || !$item1->accessEdit(\Auth::user()) || !$item2 || !$item2->accessEdit(\Auth::user())) {
			return \TAO::pageNotFound();
		}
		$v = $item1['weight'];
		$item1['weight'] = $item2['weight'];
		$item2['weight'] = $v;
		$item1->save();
		$item2->save();
		return redirect($this->actionUrl('list'));
	}

	/**
	 * @param $page
	 * @return mixed
	 */
	public function pageUrl($page)
	{
		return $this->actionUrl('list', array('page' => $page));
	}

	/**
	 * @return array
	 */
	protected function prepareRows()
	{
		$rows = array();
		foreach ($this->selectRows() as $row) {
			$this->prepareRow($row);
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * @param $tree
	 */
	protected function prepareTree($tree)
	{
		foreach ($tree as $row) {
			$this->prepareRow($row);
			if (isset($row->children) && is_array($row->children)) {
				$this->prepareTree($row->children);
			}
		}
	}

	/**
	 * @param $row
	 */
	protected function prepareRow($row)
	{
		$row->prepareForAdminList();
		if ($row->canViewInAdmin()) {
			$this->canView = true;
		}
		if ($row->accessEdit(\Auth::user())) {
			$this->canEdit = true;
		}
		if ($row->accessDelete(\Auth::user())) {
			$this->canDelete = true;
		}
	}

	/**
	 * @param $method
	 * @param string $mode
	 * @return array
	 */
	protected function generateFields($method, $mode = 'default')
	{
		$validationMethod = "in{$method}";
		$weightMethod = "weightIn{$method}";
		$fields = array();
		foreach ($this->datatype()->setFieldsMode($mode)->fieldsObjects() as $name => $field) {
			if ($field->$validationMethod()) {
				$field->setupDefault();
				$fields[$name] = $field;
			}
		}
		uasort($fields, function ($f1, $f2) use ($weightMethod) {
			$w1 = $f1->$weightMethod();
			$w2 = $f2->$weightMethod();
			if ($w1 > $w2) {
				return 1;
			}
			if ($w1 < $w2) {
				return -1;
			}
			return 0;
		});
		return $fields;
	}

	protected function listFields()
	{
		return $this->generateFields('AdminList', 'list');
	}

	protected function csvFields()
	{
		if (method_exists($this->datatype(), 'csvFields')) {
			return $this->datatype()->csvFields();
		}
		return $this->generateFields('CSV', 'csv');
	}


	protected function titleList()
	{
		return $this->datatype()->adminTitleList();
	}

	protected function perPage()
	{
		if ($perPage = \request()->get('per_page', false)) {
			return $perPage;
		}
		return $this->datatype()->adminPerPage();
	}

	protected function currentPage()
	{
		return $this->page;
	}

	protected function filtered()
	{
		if ($this->order) {
			$builder = $this->datatype()->withOrder($this->order);
		} else {
			$builder = $this->datatype()->ordered();
		}
		$builder = $this->datatype()->applyFilter($builder, $this->filterValues());
		$builder = $this->datatype()->adminModifyBuilder($builder);
		return $builder;
	}

	protected function countRows()
	{
		return $this->filtered()->count();
	}

	protected function selectRows()
	{
		return $this->filtered()
			->limit($this->perPage())
			->offset(($this->currentPage() - 1) * $this->perPage())
			->get();
	}

	public function fieldsexportAction()
	{
		$fields = $this->exportArray($this->datatype()->fields());
		$groups = $this->exportArray($this->datatype()->adminFormGroups());
		print "<pre>\tpublic function fields()\n\t{\n\t\treturn {$fields};\n\t}\n\t\n\tpublic function adminFormGroups()\n\t{\n\t\treturn {$groups};\n\t}\n</pre>";
	}

	protected function exportArray($var)
	{
		$var = var_export($var, true);
		$var = preg_replace('{=>\s+array}ism', '=> array', $var);
		$var = preg_replace('{array\s+\(}i', 'array(', $var);
		$out = '';
		foreach(explode("\n", $var) as $line) {
			$out .= "\t\t".preg_replace("{\G {2}}","\t", rtrim($line)). "\n";
		}
		return trim($out);
	}

}
