<?php

namespace TAO\Fields\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TAO\Fields\Exception\UndefinedField;

/**
 * Class API
 * @package TAO\Fields\Controllers
 */
class API extends \TAO\Controller
{
	/**
	 * @var
	 */
	public $datatypeCode;
	/**
	 * @var
	 */
	public $fieldName;
	/**
	 * @var
	 */
	public $datatype;
	/**
	 * @var
	 */
	public $item;
	/**
	 * @var
	 */
	public $id;
	/**
	 * @var
	 */
	public $field;
	/**
	 * @var
	 */
	public $action;

	/**
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function index()
	{
		$this->datatypeCode = \Request::get('datatype');
		$this->fieldName = \Request::get('field');
		$this->action = \Request::get('action');
		if (!empty($this->datatypeCode) && !empty($this->fieldName) && !empty($this->action)) {
			$this->datatype = $this->datatypeCode == '_vars' ? \TAO::vars() : \TAO::datatype($this->datatypeCode);
			if ($this->datatype) {
				$this->item = $this->datatype;
				$this->id = \Request::get('id');
				if (!empty($this->id)) {
					$this->item = $this->datatype->find($this->id);
					if (!$this->item) {
						return $this->error("Item {$this->id} not found");
					}
				}
				try {
					$this->field = $this->item->field($this->fieldName);
				} catch (UndefinedField $e) {
					return $this->error("Field {$this->field} not found");
				}

				if ($this->field->accessAPI()) {
					$method = 'apiAction' . ucfirst(camel_case($this->action));
					if (!method_exists($this->field, $method)) {
						return $this->error("API action '{$this->action}' not found");
					}
					$rc = $this->field->$method($this);
					if ($rc instanceof Response || $rc instanceof StreamedResponse) {
						return $rc;
					}
					if (is_string($rc)) {
						return $this->error($rc);
					}
					return $this->response($rc);
				}
			}
		}
		return $this->pageNotFound();
	}

	/**
	 * @param string $message
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	protected function error($message = 'Error')
	{
		return $this->json(['error' => $message]);
	}

	/**
	 * @param $m
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	protected function response($m)
	{
		$m['error'] = false;
		return $this->json($m);
	}
}