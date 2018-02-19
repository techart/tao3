<?php

namespace TAO\ORM\Traits;

trait Admin
{
    public $adminMenuSection = 'Материалы';
    public $adminPerPage = 20;
    public $adminTitle = false;


    /**
     * @return string
     */
    public function adminController()
    {
        return '\\TAO\\Admin\\Controller\\Table@entryPointAction';
    }

    public function adminMenuSection()
    {
        return $this->adminMenuSection;
    }

    /**
     * @return string
     */
    public function adminTitle()
    {
        return $this->adminTitle ? $this->adminTitle : $this->typeTitle();
    }

    /**
     * @return int
     */
    public function adminPerPage()
    {
        return $this->adminPerPage;
    }

    /**
     * @return array|bool
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
            return count($tabs) > 0 ? $tabs : false;
        }
        return false;
    }

    /**
     * @return array
     */
    public function adminFormFields()
    {
        $add = !$this->exists;
        $fields = array();
        foreach ($this->fieldsObjects() as $name => $field) {
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
     * @return bool
     */
    public function adminFormGroups()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function adminViewsPath()
    {
        return false;
    }

    /**
     * @return string
     */
    public function adminMenuTitle()
    {
        return $this->adminTitle();
    }

    /**
     * @return string
     */
    public function adminTitleList()
    {
        return $this->adminTitle();
    }

    /**
     * @return string
     */
    public function adminTitleEdit()
    {
        return $this->adminTitle() . ': Редактирование';
    }

    /**
     * @return string
     */
    public function adminTitleAdd()
    {
        return $this->adminTitle() . ': Добавление';
    }

    /**
     *
     */
    public function prepareForAdminList()
    {
    }

    /**
     * @return string
     */
    public function adminAddButtonText()
    {
        return 'Добавить';
    }

    /**
     * @return string
     */
    public function adminAddSubmitText()
    {
        return $this->adminAddButtonText();
    }

    /**
     * @return string
     */
    public function adminAddSubmitAndStayText()
    {
        return $this->adminAddButtonText() . ' и остаться';
    }

    /**
     * @return string
     */
    public function adminEditSubmitText()
    {
        return 'Изменить';
    }

    /**
     * @return string
     */
    public function adminEditSubmitAndStayText()
    {
        return $this->adminEditSubmitText() . ' и остаться';
    }

    /**
     * @return string
     */
    public function adminReturnToListText()
    {
        return 'Вернуться к списку';
    }

    /**
     * @return string
     */
    public function adminEmptyListText()
    {
        return 'Нет ни одной записи';
    }

    public function titleForAdminList()
    {
        return $this->title();
    }

    public function titleForTreeAdmin($level = 0)
    {
        return $this->titleForAdminList();
    }

    public function filter()
    {
        return false;
    }
}