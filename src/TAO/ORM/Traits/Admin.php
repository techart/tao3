<?php

namespace TAO\ORM\Traits;

trait Admin
{
	public $adminMenuSection = 'Материалы';
	public $adminPerPage = 20;
	public $adminTitle = false;


	/**
	 * Возвращает админский контроллер и точку входа в него для данного дататайпа
	 * как строку вида "\Класс\Админского\Контроллера@имяМетода" (формат, используемый в стандартном роутинге)
	 * Метод следует переопределять если не устраивает стандартный админский контроллер, и вы создаете свой
	 *
	 * @return string
	 */
	public function adminController()
	{
		return '\\TAO\\Admin\\Controller\\Table@entryPointAction';
	}

	/**
	 * Возвращает пункт админского меню первого уровня, т.е. секцию меню, в которую будет помещен пункт меню второго уровня для данного дататайпа
	 * По умолчанию - "Материалы". Если, например, будет возвращена строка "Документы", то в админском меню появится такая секция.
	 * Для лаконичности рекомендуется переопределять не этот метод, а переменную класса public $adminMenuSection
	 * Если не хотите, чтобы данный дататайп светитлся в админском меню, то верните здесь false
	 *
	 * @return string
	 */
	public function adminMenuSection()
	{
		return $this->adminMenuSection;
	}

	/**
	 * Заголовок (человеческое название) типа в админской части.
	 * По умолчанию, строка, возвращаемая этим методом используется в качестве названия ссылки в админском меню,
	 * а также как заголовок страницы админского контроллера
	 * Для лаконичности рекомендуется переопределять не этот метод, а переменную класса public $adminTitle
	 *
	 * @return string
	 */
	public function adminTitle()
	{
		return $this->adminTitle ? $this->adminTitle : $this->typeTitle();
	}

	/**
	 * Количество записей на одной странице в админском табличном контроллере. Для древовидных дататайпов это значение игнорируется (все выводятся на одной странице)
	 * Для лаконичности рекомендуется переопределять не этот метод, а переменную класса public $adminPerPage
	 *
	 * @return int
	 */
	public function adminPerPage()
	{
		return $this->adminPerPage;
	}

	/**
	 * Список вкладок на многовкладочных формах.
	 * Обычно вкладки формируются на основании метода adminFormGroups, поэтому переопределять этот метод рекомендуется в совсем уж страшных случаях.
	 *
	 * @return array
	 */
	public function adminFormTabs()
	{
		$groups = $this->adminFormGroups();
		$tabs = array();
		if (is_array($groups)) {
			foreach ($groups as $code => $label) {
				if (strpos($code, '.') === false) {
					$tabs[$code] = $label;
				}
			}
			if (count($tabs) > 0) {
				return $tabs;
			};
		}
		return [];
	}

	/**
	 * Список групп полей для админской формы.
	 * На основании этого списка формируется список вкладок и блоков внутри вкладок
	 *
	 * @return array
	 */
	public function adminFormGroups()
	{
		return method_exists($this, 'groupsHelper') ? $this->groupsHelper() : [];
	}

	/**
	 * Список полей для админской формы.
	 * В обычных случаях переопределение этого метода не требуется, т.к. отсортированнй список формируется на основании параметров, возвращаемых методом fields()
	 *
	 * @return array
	 */
	public function adminFormFields()
	{
		$add = !$this->exists;
		$fields = array();
		foreach ($this->setFieldsMode('form')->fieldsObjects() as $name => $field) {
			$method = $add ? 'inAdminAddForm' : 'inAdminEditForm';
			if ($field->$method()) {
				$fields[$name] = $field;
			}
		}
		uasort($fields, function ($f1, $f2) {
			$w1 = $f1->weightInAdminForm();
			$w2 = $f2->weightInAdminForm();
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

	/**
	 * Путь к дополнительному каталогу с шаблонами для админки. Если он указан, то шаблоны будут сначала искаться там.
	 *
	 * @return bool
	 */
	public function adminViewsPath()
	{
		return false;
	}

	/**
	 * Название пункта меню в админке. Если не указан, то будет использован adminTitle
	 *
	 * @return string
	 */
	public function adminMenuTitle()
	{
		return $this->adminTitle();
	}

	/**
	 * Заголовок страницы со списком записей в админке. Если не указан, то будет использован adminTitle
	 *
	 * @return string
	 */
	public function adminTitleList()
	{
		return $this->adminTitle();
	}

	/**
	 * Заголовок страницы редактирования в админке.
	 * Если не указан, то будет сгенерирован автоматически на основе adminTitle
	 *
	 * @return string
	 */
	public function adminTitleEdit()
	{
		return $this->adminTitle() . ': Редактирование';
	}

	/**
	 * Заголовок страницы добавления в админке.
	 * Если не указан, то будет сгенерирован автоматически на основе adminTitle
	 *
	 * @return string
	 */
	public function adminTitleAdd()
	{
		return $this->adminTitle() . ': Добавление';
	}

	/**
	 * Действия над записью, совершаемые перед показом в админском списке
	 *
	 */
	public function prepareForAdminList()
	{
	}

	/**
	 * Текст кнопки перехода на форму добавления.
	 * По умолчанию - "Добавить"
	 *
	 * @return string
	 */
	public function adminAddButtonText()
	{
		return 'Добавить';
	}

	/**
	 * Текст сабмит-кнопки на форме добавления
	 * По умолчанию равен тексту кнопки перехода на форму добавления
	 *
	 * @return string
	 */
	public function adminAddSubmitText()
	{
		return $this->adminAddButtonText();
	}

	/**
	 * Текст кнопки "Добавить и остаться".
	 * По умолчанию генерируется автоматически на основе текста кнопки "Добавить"
	 *
	 * @return string
	 */
	public function adminAddSubmitAndStayText()
	{
		return $this->adminAddButtonText() . ' и остаться';
	}

	/**
	 * Текст сабмит-кнопки на форме редактирования.
	 * По умолчанию - "Изменить"
	 *
	 * @return string
	 */
	public function adminEditSubmitText()
	{
		return 'Изменить';
	}

	/**
	 * Текст кнопки "Изменить и остаться".
	 * По умолчанию генерируется автоматически на основе текста кнопки "Изменить"
	 *
	 * @return string
	 */
	public function adminEditSubmitAndStayText()
	{
		return $this->adminEditSubmitText() . ' и остаться';
	}

	/**
	 * Текст кнопки "Вернуться к списку"
	 *
	 * @return string
	 */
	public function adminReturnToListText()
	{
		return 'Вернуться к списку';
	}

	/**
	 * Текст сообщения когда нет ни одной записи
	 *
	 * @return string
	 */
	public function adminEmptyListText()
	{
		return 'Нет ни одной записи';
	}

	/**
	 * Заголовок записи, используемый при формировании списков в админке
	 *
	 * @return mixed
	 */
	public function titleForAdminList()
	{
		return $this->title();
	}

	/**
	 * Заголовок записи, используемый при формировании дерева в админке древовидных данных
	 * В метод передается номер уровня (начиная с нуля)
	 *
	 * @param int $level
	 * @return mixed
	 */
	public function titleForTreeAdmin($level = 0)
	{
		return $this->titleForAdminList();
	}

	/**
	 * Список полей, используемых в фильтре.
	 * По умолчанию формируется на основе метода fields(), но зачастую требует переопределения в случае сложных фильтров
	 *
	 * @return array
	 */
	public function filter()
	{
		$filterFields = [];
		foreach ($this->fields() as $fieldName => $fieldData) {
			if (isset($fieldData['in_filter']) && $fieldData['in_filter']) {
				$filterFields[$fieldName] = $fieldData;
			}
		}
		return $filterFields;
	}

	public function adminModifyBuilder($builder)
	{
		return $builder;
	}

	public function titleForFilter($name, $datatype = false)
	{
		if (request()->has('filter')) {
			$filter = request()->get('filter');
			if (isset($filter[$name])) {
				$value =  $filter[$name];
				if ($datatype) {
					if ($item = \TAO::datatype($datatype)->find($value)) {
						return $item->title();
					}
					return;
				}
				return $value;
			}
		}
	}

	public function canViewInAdmin()
	{
		return false;
	}
}
