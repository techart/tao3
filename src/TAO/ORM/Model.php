<?php

namespace TAO\ORM;

use Illuminate\Database\Query\Builder;
use Ramsey\Uuid\UuidInterface;
use TAO\Fields;
use Ramsey\Uuid\Uuid;
use TAO\ORM\Exception\NonStrorableObjectSaving;
use TAO\ORM\Traits\Tree;
use TAO\Selector;
use TAO\Type\Collection;

/**
 * Class Model
 * @package TAO\ORM
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
	use Traits\Schema,
		Traits\Events,
		Traits\Access,
		Traits\Admin,
		Traits\Urls,
		Traits\View;

	/**
	 * @var bool
	 */
	public $incrementing = false;
	/**
	 * @var array
	 */
	public $typeTitle = false;

	/**
	 * @var bool
	 */
	protected $isDatatype = false;

	/**
	 * @var string
	 */
	protected $parentKeyField = 'parent_id';

	/**
	 * @var string
	 */
	protected $idType = 'auto_increment';

	/**
	 * @var array
	 */
	protected $fields = array();

	/**
	 * @var array
	 */
	protected $extraFields = array();

	/**
	 * @var
	 */
	protected $processedFields;

	/**
	 * @var array
	 */
	protected $errors = array();


	/**
	 * Model constructor.
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		$this->setConnection(app()->tao->connectionNameFor($this->getTable()));
		if ($this->idType == 'auto_increment') {
			$this->incrementing = true;
		}
		$this->updateSchemaIfNecessary();
		$this->setupFields();
		if (!$this->typeTitle) {
			$this->typeTitle = get_class($this);
		}
	}

	/**
	 * @return mixed
	 */
	public function getDatatype()
	{
		return \TAO::datatypeCodeByClass(get_class($this));
	}

	public function getDatatypeObject()
	{
		return \TAO::datatype($this->getDatatype());
	}

	public function initDatatype()
	{
		$this->isDatatype = true;
	}

	public function isDatatype()
	{
		return $this->isDatatype;
	}

	/**
	 * @return array
	 */
	public function errors()
	{
		return $this->errors;
	}

	/**
	 * @param $message
	 * @param null $column
	 */
	public function error($message, $column = null)
	{
		if ($column) {
			$this->errors[$column] = $message;
		} else {
			$this->errors[] = $message;
		}
	}

	/**
	 * @return mixed
	 */
	abstract public function fields();

	protected function initExtraFields()
	{
	}

	protected function initExtra()
	{
		foreach (func_get_args() as $arg) {
			$method = "initExtra{$arg}";
			$this->$method();
		}
	}

	public function checkIfTree()
	{
		return method_exists($this, 'isTree') ? $this->isTree() : false;
	}

	public function checkIfSortable()
	{
		return method_exists($this, 'isSortable') ? $this->isSortable() : false;
	}

	/**
	 * @return mixed
	 */
	public function calculatedFields()
	{
		$fields = $this->fields();
		$this->initExtraFields();
		foreach ($this->extraFields as $field => $data) {
			if (isset($fields[$field])) {
				if ($fields[$field] === false) {
					unset($fields[$field]);
				} else {
					$fields[$field] = \TAO::merge($data, $fields[$field]);
				}
			} else {
				$fields[$field] = $data;
			}
		}
		return $fields;
	}

	/**
	 * @return array
	 */
	protected function processedFields()
	{
		if (is_null($this->processedFields)) {
			$count = 0;
			$this->processedFields = array();
			foreach ($this->calculatedFields() as $field => $data) {
				$count++;
				if (!isset($data['weight'])) {
					$data['weight'] = $count;
				}
				$this->processedFields[$field] = $data;
			}
		}
		return $this->processedFields;
	}

	/**
	 * @return Fields\Field[]
	 * @throws Fields\Exception\UndefinedField
	 */
	public function fieldsObjects()
	{
		$fields = array();
		foreach (array_keys($this->calculatedFields()) as $name) {
			$fields[$name] = $this->field($name);
		}
		return $fields;
	}

	/**
	 * @param $name
	 * @param bool $forceType
	 * @return Fields\Field
	 * @throws Fields\Exception\UndefinedField
	 */
	public function field($name, $forceType = false)
	{
		if ($forceType) {
			$fields = $this->processedFields();
			if (!isset($fields[$name])) {
				throw new Fields\Exception\UndefinedField($name, get_class($this));
			}
			$data = $fields[$name];
			$data['type'] = $forceType;
			return app('tao.fields')->create($name, $data, $this);
		}
		if (!isset($this->fields[$name])) {
			$fields = $this->processedFields();
			if (!isset($fields[$name])) {
				throw new Fields\Exception\UndefinedField($name, get_class($this));
			}
			$this->fields[$name] = app('tao.fields')->create($name, $fields[$name], $this);
		}
		return $this->fields[$name];
	}

	/**
	 * @throws Fields\Exception\UndefinedField
	 */
	public function setupFields()
	{
		foreach (array_keys($this->calculatedFields()) as $field) {
			$this->field($field)->setupDefault();
		}
	}

	/**
	 * @return UuidInterface
	 */
	public function generateNewId()
	{
		return Uuid::uuid4();
	}

	public function title()
	{
		return isset($this->attributes['title']) ? $this->attributes['title'] : $this->getKey();
	}

	/**
	 * @param $builder
	 * @param $filter
	 * @return $this
	 */
	public function applyFilter($builder, $filter)
	{
		foreach ($filter as $name => $value) {
			if (!empty($value)) {
				$method = 'applyFilter' . camel_case($name);
				if (method_exists($this, $method)) {
					$this->$method($builder, $value);
				}
			}
		}
		return $builder;
	}

	/**
	 * @return Builder
	 */
	public function ordered()
	{
		return $this->orderBy($this->getKeyName());
	}

	/**
	 * @param bool $args
	 * @return array
	 */
	public function itemsForSelect($args = false)
	{
		$args = Collection::parseString($args);
		if ($this->checkIfTree()) {
			/** @var Tree $this */
			return $this->treeForSelect($args);
		}
		$out = Collection::numericKeysOnly($args);
		foreach ($this->ordered()->get() as $row) {
			$out[$row->getKey()] = $row->title();
		}
		return $out;
	}

	protected function getHomeSubDir()
	{
		$id = $this->getKey();
		if (empty($id)) {
			return false;
		}
		if (is_int($id)) {
			$p1 = str_pad(floor($id / 1000), 4, '0', STR_PAD_LEFT);
			$p2 = str_pad($id, 8, '0', STR_PAD_LEFT);
		} else {
			$p1 = substr($id, 0, 2);
			$p2 = $id;
		}
		return 'datatypes/' . $this->getDatatype() . "/{$p1}/{$p2}";
	}

	public function getHomeDir()
	{
		$sub = $this->getHomeSubDir();
		if (!$sub) {
			return false;
		}
		$dir = "public/{$sub}";
		if (!\Storage::exists($dir)) {
			\Storage::makeDirectory($dir);
		}
		return $dir;
	}

	public function getPrivateHomeDir()
	{
		$dir = $this->getHomeSubDir();
		if (!\Storage::exists($dir)) {
			\Storage::makeDirectory($dir);
		}
		return $dir;
	}

	/**
	 * Возвращает запись по id
	 *
	 * @param $id
	 * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|Model|Model[]
	 */
	public function getItemById($id)
	{
		return $this->find($id);
	}

	/**
	 * Возвращает список итемов (query), доступных для чтения текущему пользователю
	 *
	 * @param array $data
	 * @return Builder
	 */
	public function getAccessibleItems($data = [])
	{
		return $this->ordered();
	}

	public function typeTitle()
	{
		return $this->typeTitle;
	}

	public function selector()
	{
		$selector = app()->make(Selector::class);
		$selector->mnemocode = $this->getDatatype();
		$selector->datatype = $this;
		$selector->title = $this->typeTitle();
		return $selector;
	}

	public function validateField($name)
	{
		$cname = ucfirst(camel_case($name));
		$method = "validateField{$cname}";
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		return $this->field($name)->validate();
	}

	public function validate()
	{
		foreach ($this->calculatedFields() as $name => $data) {
			$v = $this->validateField($name);
			if (is_string($v)) {
				$this->error($v, $name);
			}
		}
	}

	public function validateForAdmin()
	{
		return $this->validate();
	}

	protected function triggerEventForFields($eventName, $data = [])
	{
		foreach ($this->fieldsObjects() as $field) {
			call_user_func_array([$field, $eventName], $data);
		}
	}

	public function beforeInsert()
	{
		$this->triggerEventForFields('beforeItemInsert');
	}

	public function afterInsert()
	{
		$this->triggerEventForFields('afterItemInsert');
	}

	public function beforeSave()
	{
		$this->triggerEventForFields('beforeItemSave');
	}

	public function afterSave()
	{
		$this->triggerEventForFields('afterItemSave');
	}

	public function beforeUpdate()
	{
		$this->triggerEventForFields('beforeItemUpdate');
	}

	public function afterUpdate()
	{
		$this->triggerEventForFields('afterItemUpdate');
	}

	public function beforeDelete()
	{
		$this->triggerEventForFields('beforeItemDelete');
	}

	public function afterDelete()
	{
		$this->triggerEventForFields('afterItemDelete');
	}

	public function save(array $options = [])
	{
		if (!$this->isStrobale()) {
			$message = "Аttempt to save non-storable object '" . get_class($this) . "'.";
			if ($this->isDatatype()) {
				$message .= " Datatype object can't be saved.";
			}
			throw new NonStrorableObjectSaving($message);
		}
		return parent::save($options);
	}

	protected function isStrobale()
	{
		return !$this->isDatatype();
	}

	public function __call($method, $args)
	{
		if ($m = \TAO::regexp('{^(.+)_belongs_to_many$}', $method)) {
			/** @var Fields\Type\Multilink $field */
			$field = $this->field($m[1]);
			return $field->belongsToMany();
		}
		return parent::__call($method, $args);
	}
}