<?php

namespace TAO\Fields;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use TAO\Callback;
use TAO\Foundation\Request;
use TAO\ORM\Model;
use TAO\Schema\Index\Builder;
use TAO\Text\StringTemplate;
use TAO\Type;

/**
 *
 * Абстрактный класс, от которого пораждаются конкретные типы филдов
 *
 * Class Field
 * @package TAO\Fields
 */
abstract class Field
{
	/**
	 *
	 * Мнемокод типа (string, text, checkbox и пр.)
	 * Переопределения не требует, заполняется автоматически
	 *
	 * @var string
	 */
	public $type;

	/**
	 *
	 * Массив параметров поля как он описан в fields() модели
	 * Переопределения не требует, заполняется автоматически
	 *
	 * @var array
	 */
	public $data;


	/**
	 *
	 * Итем, к которому привязано поле
	 * Переопределения не требует, заполняется автоматически
	 *
	 * @var Model
	 */
	public $item;

	/**
	 *
	 * Имя поля
	 * Переопределения не требует, заполняется автоматически
	 *
	 * @var string
	 */
	public $name;


	/**
	 *
	 * Расширяющие параметры типа - результат парсинга параметра type, т.е строки вида "string(200) index(f1, f2)"
	 * Переопределения не требует, заполняется автоматически
	 *
	 * @var array
	 */
	public $params;

	/**
	 *
	 * Здесь хранится значение поля если оно не предназначено для хранения в БД. В другом случае значение
	 * хранится в атрибутах модели.
	 *
	 * @var mixed
	 */
	protected $value;


	/**
	 *
	 * Проверка существования поля (полей) в таблице БД. Если поля нет, то оно создается
	 * Чаще всего переопределение требуется в случае сложного поля (создание сопутствующих таблиц и т.п.)
	 *
	 * @param Blueprint $table
	 * @return $this
	 */
	public function checkSchema(Blueprint $table)
	{
		if ($this->isCreateFieldRequired()) {
			if (!$this->item->hasColumn($this->name)) {
				$this->createField($table);
			} else {
				$f = $this->createField($table);
				if ($f) {
					$f->change();
				}
			}
		}
		return $this;
	}

	protected function isCreateFieldRequired()
	{
		return $this->isStorable();
	}

	protected function isStorable()
	{
		return $this->param('storable', true);
	}

	/**
	 *
	 * Создание поля в таблице БД
	 *
	 * @param Blueprint $table
	 * @return Fluent|void
	 */
	public function createField(Blueprint $table)
	{

	}

	/**
	 * Проверка индексов в таблице БД.
	 * Как правило, переопределение не требуется. Описание индексов берется из параметра type - строки вида "string(200) index(f1, f2)"
	 *
	 * @param Blueprint $table
	 * @return $this
	 */
	public function checkIndexes(Blueprint $table)
	{
		$index = false;
		foreach (['index', 'unique', 'fulltext'] as $type) {
			if (isset($this->params[$type])) {
				$index = $this->params[$type];
			}
		}
		if ($index) {
			$type = $index['name'];
			$name = $index['extra'] ? $index['extra'] : $this->indexName();
			$columns = $index['args'] ? $index['args'] : array($this->name);

			/**
			 * @var Builder $indexBuilder
			 */
			$indexBuilder = app()->make('\TAO\Schema\Index\Builder');
			$index = $indexBuilder->makeIndex($name, $columns, $type);
			$indexBuilder->process($index, $table, $this->item->getConnection());
		}
		return $this;
	}

	/**
	 *
	 * Запись в итем значения.
	 *
	 * @param $value
	 */
	public function set($value)
	{
		if ($this->isStorable()) {
			$this->item[$this->name] = $value;
		} else {
			$this->value = $value;
		}
	}

	/**
	 * @param Request $request
	 */
	public function setFromRequest($request)
	{
		$value = null;
		if ($this->hasRequestValue($request)) {
			$value = $this->getValueFromRequest($request);
		}
		$this->set(!is_null($value) ? $value : $this->nullValue());
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	protected function hasRequestValue($request)
	{
		return $request->has($this->name);
	}

	/**
	 * @param $requestData
	 */
	public function setFromRequestData($requestData)
	{
		$this->set(isset($requestData[$this->name]) ? $requestData[$this->name] : $this->nullValue());
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	protected function getValueFromRequest($request)
	{
		return $request->input($this->name);
	}

	/**
	 * @param $request
	 */
	public function setFromRequestAfterSave($request)
	{
	}

	/**
	 * @param Request $request
	 */
	public function setFromFilter($request)
	{
		if ($request->has('filter')) {
			$this->setFromRequestData($request->input('filter'));
		}
	}

	/**
	 * @return string
	 */
	public function defaultValue()
	{
		return '';
	}

	/**
	 * @return string
	 */
	public function nullValue()
	{
		return '';
	}

	/**
	 * @return $this
	 */
	public function setupDefault()
	{
		if (!$this->itemHasValue()) {
			$value = $this->defaultValue();
			if (isset($this->data['default'])) {
				$def = $this->data['default'];
				if (is_string($this->data['default']) && $m = \TAO::regexp('{^filter\[([a-z0-9_]+)\]$}', $def)) {
					$key = trim($m[1]);
					if (request()->has('filter')) {
						$filter = request()->get('filter');
						if (isset($filter[$key])) {
							$value = $filter[$key];
						}
					}
				} else {
					$value = $def;
				}
			}
			$this->setDefault($value);
		}
		return $this;
	}

	public function setDefault($value)
	{
		return $this->set($value);
	}

	/**
	 * @return bool
	 */
	protected function itemHasValue()
	{
		return !is_null($this->item->getAttributeValue($this->name));
	}

	/**
	 * @param $action
	 * @param array $args
	 * @return string
	 */
	public function apiUrl($action, $args = array())
	{
		$args['action'] = trim($action);
		$args['datatype'] = $this->item->getDatatype();
		$args['field'] = $this->name;

		$id = $this->item->getKey();
		if (!empty($id)) {
			$args['id'] = $id;
		}
		return '/tao/fields/api?' . http_build_query($args);
	}

	/**
	 * @param bool|false $user
	 * @return mixed
	 */
	public function accessAPI($user = false)
	{
		if (!$user) {
			$user = \Auth::user();
		}
		return $this->item->accessEdit($user);
	}

	/**
	 * @return mixed
	 */
	public function value()
	{
		return $this->prepareValue($this->rawValue());
	}

	/**
	 * @return mixed
	 */
	public function rawValue()
	{
		if ($this->isStorable()) {
			return $this->item[$this->name];
		} else {
			return $this->value;
		}
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	protected function prepareValue($value)
	{
		if (isset($this->data['prepare_value']) && Type::isCallable($this->data['prepare_value'])) {
			$value = Callback::instance($this->data['prepare_value'])->call($value, $this);
		}
		return $value;
	}

	/**
	 * Метод возвращает true если у поля нет значения для отображения в шаблоне. Учитывает callback 'is_empty'
	 * в настройках поля(если он задан).
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		if (isset($this->data['is_empty']) && Type::isCallable($this->data['is_empty'])) {
			return Callback::instance($this->data['is_empty'])->call($this, $this->item);
		}
		return $this->checkEmpty();
	}

	/**
	 * Метод возвращает true если у поля есть значения для отображения в шаблоне.
	 *
	 * @return bool
	 */
	public function checkEmpty()
	{
		return $this->value() == $this->nullValue();
	}

	/**
	 * Метод возвращает true если у поля нет значения для отображения в шаблоне. Учитывает callback 'is_empty'
	 * в настройках поля(если он задан).
	 *
	 * @return bool
	 */
	public function isNotEmpty()
	{
		return !$this->isEmpty();
	}

	/**
	 *
	 * Имя дефолтного шаблона для рендера аутпута. Если возвращает false, то аутпут по умолчанию рендерится без шаблона
	 *
	 * @return string|bool
	 */

	protected function defaultTemplate()
	{
		return false;
	}

	/**
	 *
	 * Дефолтный контекст, который передается в шаблон аутпута
	 *
	 * @return array
	 */

	protected function defaultContext()
	{
		return [
			'field' => $this,
			'item' => $this->item,
			'settings' => $this->data,
		];
	}

	/**
	 *
	 * Рендер значения
	 *
	 * @param $arg1 - имя шаблона или контекст (если шаблон стандартный)
	 * @param $arg2 - контекст
	 *
	 * @return string
	 */
	public function render($arg1 = false, $arg2 = false)
	{
		$template = $this->defaultTemplate();
		if (is_string($arg1)) {
			$template = $arg1;
		} elseif (isset($this->data['template'])) {
			$template = $this->data['template'];
		}

		if ($template) {
			$context = $this->defaultContext();
			if (is_array($arg1)) {
				$context = array_merge($context, $arg1);
			} elseif (is_array($arg2)) {
				$context = array_merge($context, $arg2);
			}
			return view($template, $context);
		} else {
			return $this->renderWithoutTemplate();
		}
	}

	public function renderWithoutTemplate()
	{
		return $this->value();
	}

	/**
	 *
	 * Имя дефолтного шаблона для рендера инпута в форме
	 *
	 * @return string
	 */
	public function defaultInputTemplate()
	{
		if (isset($this->data['input_template'])) {
			return $this->data['input_template'];
		}
		return "fields.{$this->inputTemplateFrom()}.input";
	}

	/**
	 *
	 * Имя дефолтного шаблона для рендера инпута в публичной форме
	 *
	 * @return string
	 */
	public function defaultPublicInputTemplate()
	{
		if (isset($this->data['public_input_template'])) {
			return $this->data['public_input_template'];
		}
		$tpl = "fields.{$this->inputTemplateFrom()}.public-input";
		if (app(\Illuminate\View\Factory::class)->exists($tpl)) {
			return $tpl;
		}
		return $this->defaultInputTemplate();
	}

	/**
	 *
	 * Мнемокод типа, из которого надо брать дефолтный шаблон инпута (по умолчанию - свой)
	 *
	 * @return string
	 */
	public function inputTemplateFrom()
	{
		return $this->type;
	}


	/**
	 *
	 * Дефолтный контекст, который передается в шаблон инпута
	 *
	 * @return array
	 */
	public function defaultInputContext()
	{
		return $this->defaultContext();
	}

	/**
	 *
	 * Дефолтный контекст, который передается в шаблон инпута в публичной форме
	 *
	 * @return array
	 */
	public function defaultPublicInputContext()
	{
		return $this->defaultContext();
	}

	/**
	 *
	 * Рендер инпута в форме
	 *
	 * @param $arg1 - имя шаблона или контекст (если шаблон стандартный)
	 * @param $arg2 - контекст
	 *
	 * @return string
	 */
	public function renderInput($arg1 = false, $arg2 = false)
	{
		$template = $this->defaultInputTemplate();
		if (is_string($arg1)) {
			$template = $arg1;
		} elseif (isset($this->data['input_template'])) {
			$template = $this->data['input_template'];
		}
		if ($template) {
			$context = $this->defaultInputContext();
			if (is_array($arg1)) {
				$context = array_merge($context, $arg1);
			} elseif (is_array($arg2)) {
				$context = array_merge($context, $arg2);
			}
			return view($template, $context);
		} else {
			return 'No input template for field ' . get_class($this);
		}
	}

	/**
	 * Рендер инпута для публичной формы если для этого типа поля есть отдельный шаблон
	 *
	 * @param bool $arg1
	 * @param bool $arg2
	 */
	public function renderPublicInput($arg1 = false, $arg2 = false)
	{
		$template = $this->defaultPublicInputTemplate();
		if (is_string($arg1)) {
			$template = $arg1;
		}

		if ($template) {
			$context = $this->defaultPublicInputContext();
			if (is_array($arg1)) {
				$context = array_merge($context, $arg1);
			} elseif (is_array($arg2)) {
				$context = array_merge($context, $arg2);
			}
			return view($template, $context);
		} else {
			return 'No input template for field ' . get_class($this);
		}
	}


	/**
	 * @param $name
	 * @param null $default
	 * @return null
	 */
	public function param($name, $default = null)
	{
		if (is_array($name)) {
			foreach ($name as $_name) {
				if (isset($this->data[$_name])) {
					return $this->data[$_name];
				}
			}
			return $default;
		}
		return isset($this->data[$name]) ? $this->data[$name] : $default;
	}

	/**
	 * @return mixed
	 */
	public function typeParamsExtra()
	{
		return $this->params['type']['extra'];
	}

	/**
	 * Метод предназначен для получения значений параметров, которые могут быть представлены в виде callback. Если
	 * параметр задан и он исполняемый, то возвращается результат выполнения данного callback, в обратном случае
	 * возвращается значение. Если параметр не задан, но передано значение по умолчанию, то вышеописанный алгоритм
	 * применяется к этому значению.
	 *
	 * Аргументы, переданные в параметре $args будут отправлены в коллбэк. В метод они должны приходить в виде массива,
	 * в callback будут переданы в виде списка аргументов.
	 *
	 * Переданный контекст будет использован если значение параметра $name будет строкой и не будет являться выполняемым
	 * значениемй. В этом случае будет проверяться есть ли метод $name у контекста. Если да, то будет вызван он.
	 *
	 * @param string $name
	 * @param mixed $default
	 * @param array $args
	 * @param mixed $probableContext
	 * @return mixed
	 */
	public function callableParam($name, $default = null, $args = [], $probableContext = null)
	{
		$value = $this->param($name);
		$value = is_null($value) ? $default : $value;
		if (!is_null($value)) {
			if (is_string($value) && !is_null($probableContext) && Type::isCallable([$probableContext, $value])) {
				$value = [$probableContext, $value];
			}
			if (Type::isCallable($value)) {
				return Callback::instance($value)->args($args)->call();
			}
		}
		return $value;
	}

	/**
	 * @return mixed
	 */
	public function typeParamsArgs()
	{
		return $this->params['type']['args'];
	}

	/**
	 * @param int $default
	 * @return int
	 */
	public function typeParamsIntArg($default = 0)
	{
		$args = $this->typeParamsArgs();
		if (is_array($args)) {
			foreach ($args as $arg) {
				if (preg_match('{^\d+$}', $arg)) {
					return (int)$arg;
				}
			}
		}
		return $default;
	}

	/**
	 * @param array $enum
	 * @param bool|false $default
	 * @return bool|string
	 */
	public function typeParamsEnumArg(array $enum, $default = false)
	{
		$args = $this->typeParamsArgs();
		if (is_array($args)) {
			foreach ($args as $arg) {
				$arg = strtolower($arg);
				if (in_array($arg, $enum)) {
					return $arg;
				}
			}
		}
		return $default;
	}

	/**
	 * @param $message
	 * @param null $column
	 */
	public function error($message, $column = null)
	{
		$this->item->error($message, $column);
	}

	public function renderForAdminView()
	{
		return $this->renderForAdmin('view');
	}

	public function renderForAdminList()
	{
		$value = $this->renderForAdmin('list');
		$handlers = $this->param('list_value_handlers', false);
		if (is_array($handlers)) {
			$value = $this->applyHandlers($value, $handlers);
		}
		return $value;
	}
	
	public function applyHandlers($value, $handlers = [])
	{
		foreach($handlers as $handler) {
			if ($handler = trim($handler)) {
				if ($m = \TAO::regexp('{^(.+)\.([^.]+)$}', $handler)) {
					$dt = dt($m[1]);
					$method = $m[2];
					$value = $dt->$method($value, $this->item);
				} else {
					$value = $handler($value, $this->item);
				}
			}
		}
		return $value;
	}

	/**
	 * @return mixed
	 */
	public function renderForAdmin($action)
	{
		$render = $this->callableParam(['render_in_admin_'.$action, 'render_in_'.$action], null, [$this], $this->item);
		if (is_null($render)) {
			if ($formula = $this->param('formula')) {
				$render = $this->item->calculateFormula($formula);
			} else {
				$render = $this->render();
			}
		}
		if (isset($this->data['link_in_'.$action])) {
			$url = $this->callableParam('link_in_'.$action, null, [$this], $this->item);
			$url = StringTemplate::process($url, function($key) {
				if ($key == 'id') {
					return $this->item->getKey();
				}
				return (string)$this->item->field($key)->value();
			});
			$render = trim($render);
			$render = "<a href=\"{$url}\">{$render}</a>";
		}
		$method = 'admin'.ucfirst($action).'ValuePreprocess';
		if (method_exists($this->item, $method)) {
			return $this->item->$method($render, $this);
		}
		if (strpos($render, "\n")>0 && strpos($render, '<')===false) {
			$out = '';
			foreach(explode("\n", $render) as $line) {
				if ($line = trim($line)) {
					$out .= "\n<p>{$line}</p>";
				}
			}
			return $out;
		}
		return $render;
	}

	public function csvValue()
	{
		$render = $this->callableParam('csv_value', null, [$this], $this->item);
		if (is_null($render)) {
			$render = $this->render();
		}
		return $render;
	}


	/**
	 * @return string
	 */
	public function typeForInput()
	{
		return $this->param(['form_type', 'form_input_type', 'input_type'], 'text');
	}

	/**
	 * @return null
	 */
	public function styleForInput()
	{
		if (\TAO::inAdmin()) {
			return $this->styleForAdminInput();
		}
		return $this->param(['form_style', 'style'], '');
	}

	/**
	 * @return null
	 */
	public function styleForAdminInput()
	{
		return $this->param(['admin_form_style', 'form_style', 'style'], '');
	}

	/**
	 * @return null|string
	 */
	public function classForInput()
	{
		if (\TAO::inAdmin()) {
			return $this->classForAdminInput();
		}
		$classes = $this->param(['form_class', 'class'], '');
		if (is_array($classes)) {
			$classes = implode(' ', $classes);
		}
		return $classes;
	}

	/**
	 * @return null|string
	 */
	public function classForAdminInput()
	{
		$classes = $this->param(['admin_form_class', 'form_class', 'class'], '');
		if (is_array($classes)) {
			$classes = implode(' ', $classes);
		}
		return $classes;
	}

	/**
	 */
	public function inAdminList()
	{
		return $this->param(['in_admin_list', 'in_list'], false);
	}

	/**
	 */
	public function inCSV()
	{
		return $this->param('in_csv', false);
	}

	/**
	 */
	public function weightInCSV()
	{
		return $this->param(['weight_in_csv', 'weight_in_admin_list', 'weight_in_list', 'weight'], false);
	}

	/**
	 * @return null
	 */
	public function inAdminEditForm()
	{
		return $this->param(['in_admin_edit_form', 'in_admin_form', 'in_form'], false);
	}

	/**
	 * @return null
	 */
	public function inAdminAddForm()
	{
		return $this->param(['in_admin_add_form', 'in_admin_form', 'in_form'], false);
	}

	/**
	 * @return null
	 */
	public function weightInAdminList()
	{
		return $this->param(['wight_in_admin_list', 'weight_in_list', 'weight'], 0);
	}

	/**
	 * @return null
	 */
	public function weightInAdminForm()
	{
		return $this->param(['wight_in_admin_form', 'weight_in_form', 'weight'], 0);
	}

	/**
	 * @return null
	 */
	public function labelInAdminList()
	{
		return $this->param(['label_in_admin_list', 'label_in_list', 'label'], $this->name);
	}

	/**
	 * @return null
	 */
	public function labelInAdminForm()
	{
		return $this->param(['label_in_admin_form', 'label_in_form', 'label'], $this->name);
	}

	/**
	 * @return string
	 */
	public function thAttrsInAdminList()
	{
		$attrs = trim($this->param('admin_th_attrs', ''));
		return empty($attrs) ? '' : " {$attrs}";
	}

	/**
	 * @return string
	 */
	public function tdAttrsInAdminList()
	{
		$attrs = trim($this->param('admin_td_attrs', ''));
		return empty($attrs) ? '' : " {$attrs}";
	}

	/**
	 * Возвращает массив аттрибутов или строку с аттрибутом для тега филда, например disabled или selected
	 *
	 * @return null|array|string
	 */
	public function attrs()
	{
		return $this->param(['attrs', 'attributes'], '');
	}

	/**
	 * Распечатывает аттрибуты и их значения для тега филда
	 */
	public function renderAttrs()
	{
		if (is_string($this->attrs())) {
			echo $this->attrs();
		} else {
			foreach ($this->attrs() as $attrName => $attrValue) {
				echo $attrName, '="', $attrValue, '"';
			}
		}
	}

	/**
	 * @return string
	 */
	public function adminGroupLabel()
	{
		if (isset($this->data['group'])) {
			$group = trim($this->data['group']);
			if ($group == '') {
				return '#';
			}
			$groups = $this->item->adminFormGroups();
			if (isset($groups[$group])) {
				return $groups[$group];
			}
			return $group;
		}
		return '#';
	}

	/**
	 * @return string
	 */
	public function adminTab()
	{
		if (isset($this->data['group'])) {
			$group = trim($this->data['group']);
			if ($group) {
				list($tab) = explode('.', $group);
				$tab = trim($tab);
				if ($tab) {
					return $tab;
				}
			}
		}
		return '#';
	}

	/**
	 * @return null
	 */
	public function publicLabel()
	{
		return $this->param('label', $this->item->typeTitle() . ':' . $this->name);
	}

	/**
	 * @return bool
	 */
	public function isPresent()
	{
		return !empty($this->value());
	}

	/**
	 * @param $regexp
	 * @return bool
	 */
	public function isMatch($regexp)
	{
		$value = $this->value();
		return empty($value) || \TAO::regexp($regexp, $this->value());
	}

	/**
	 * @param string|null $context
	 * @return bool|null
	 */
	public function validate($context = null)
	{
		if (isset($this->data['required'])) {
			$req = $this->data['required'];
			if ($req) {
				if (!$this->isPresent()) {
					return $this->param(['error_message_required', 'error_message'], is_string($req) ? $req : 'Fill ' . $this->publicLabel());
				}
			}
		}
		if (isset($this->data['match'])) {
			if (!$this->isMatch($this->data['match'])) {
				return $this->param(['error_message_match', 'error_message'], 'Invalid ' . $this->publicLabel());
			}
		}
		return true;
	}
	
	public function jsonValue()
	{
		return $this->value();
	}
	
	protected function dataExportValue()
	{
		return '';
	}

	public function dataExport()
	{
		if ($value = trim($this->dataExportValue())) {
			if (strlen($value)>60 || strpos($value, "\n")!==false) {
				return "\n!{$this->name}\n{$value}\n!!";
			} else {
				return "\n!{$this->name}={$value}";
			}
		}
		return '';
	}

	public function dataImport($src)
	{
	}

	protected function indexName($columns = null)
	{
		if (is_null($columns)) {
			$columns = [$this->name];
		} else if (is_string($columns)) {
			$columns = [$columns];
		}

		$name = 'idx_' . $this->item->getTable() . '_' . implode('_', $columns);
		if (strlen($name) > 64) {
			$name = substr('idx_' . $this->item->getTable(), 0, 30) . '_' . md5(implode('_', $columns));
		}
		return $name;
	}

	/**
	 * @param $name
	 * @param null $default
	 * @return mixed|null
	 */
	public function callParam($name, $default = null)
	{
		$cb = $this->param($name);
		if (Callback::isValidCallback($cb)) {
			return Callback::instance($cb)->call($this);
		}
		if (Callback::isValidCallback($default)) {
			return Callback::instance($default)->call($this);
		}
		return $default;
	}

	public function beforeItemInsert()
	{
	}

	public function afterItemInsert()
	{
	}

	public function beforeItemSave()
	{
	}

	public function afterItemSave()
	{
	}

	public function beforeItemUpdate()
	{
	}

	public function afterItemUpdate()
	{
	}

	public function beforeItemDelete()
	{
	}

	public function afterItemDelete()
	{
	}

	/**
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * @return bool
	 */
	public function isMultipartEnctypeRequired() {
		return false;
	}
}
