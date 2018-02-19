<?php

namespace TAO\ORM\Abstracts;

abstract class FormMessageModel extends \TAO\ORM\Model
{
    public function calculatedFields()
    {
        $fields = $this->fields();
        $extra = array(
            '_time' => array(
                'protected' => true,
                'type' => 'date_integer index',
                'label' => 'Дата/время',
                'default' => time(),
                'weight' => -900,
                'in_list' => true,
                'in_form' => true,
                'group' => 'common',
            ),
            '_info' => array(
                'protected' => true,
                'type' => 'text',
                'label' => 'Информация',
                'style' => 'width:90%;height: 400px',
                'weight' => -800,
                'in_list' => false,
                'in_form' => true,
                'group' => 'info',
            ),
        );
        foreach (array_keys($fields) as $field) {
            if (!isset($fields[$field]['group'])) {
                $fields[$field]['group'] = 'common.form';
            }
        }
        foreach ($extra as $field => $data) {
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

    public function formField($name)
    {
        $fields = $this->processedFields();
        if (!isset($fields[$name])) {
            throw new \TAO\Fields\Exception\UndefinedField($name, get_class($this));
        }
        $data = $fields[$name];
        if (isset($data['type_in_form'])) {
            $data['type'] = $data['type_in_form'];
        }
        return app()->taoFields->create($name, $data, $this);
    }

    public function publicFields()
    {
        $fields = [];
        foreach ($this->processedFields() as $field => $data) {
            if (!isset($data['protected']) || !$data['protected']) {
                $fields[$field] = $this->formField($field);
            }
        }
        return $fields;
    }

    public function adminMenuSection()
    {
        return 'Формы';
    }

    public function htmlId($context = [])
    {
        return 'tao-form-' . $this->getDatatype();
    }

    public function formClass($context = [])
    {
        return 'tao-form tao-form-' . $this->getDatatype();
    }

	/**
	 * Настройки кнопки отправки
	 *
	 * @return array
	 */
	public function submitButtonSettings() {
    	return [
    		'text' => '>>>',
		    'input_classes' => 'submit',
		    'field_classes' => '',
	    ];
    }

	/**
	 * Возвращает класс для лейбла
	 *
	 * @return string
	 */
	public function labelClass() {
    	return '';
    }

    public function fieldClass($field)
    {
        return "tao-form-field-container tao-form-field-container-{$field}";
    }

    public function fieldLabel($field)
    {
        return $this->field($field)->param('label', $field);
    }

    public function adminFormGroups()
    {
        return array(
            'common' => 'Сообщение',
            'common.form' => 'Поля формы',
            'info' => 'Служебная информация',
        );
    }

    public function templateFields($context = [])
    {
        return $this->findView('fields', 'forms ~ fields');
    }

    public function templateField($field, $context = [])
    {
        return $this->findView("field-{$field}", 'forms ~ field');
    }

    public function renderInput($field)
    {
        return $this->field($field)->renderInput();
    }

    public function templateSubmit($context = [])
    {
        return $this->findView('submit', 'forms ~ submit');
    }

    public function templateErrors($context = [])
    {
        return $this->findView('errors', 'forms ~ errors');
    }

    public function templateAjax($context = [])
    {
        return $this->findView('ajax', 'forms ~ ajax');
    }

    public function action()
    {
        return '/forms/post/' . $this->getDatatype() . '/';
    }

    public function redirectUrl($context = [])
    {
        return '/forms/ok/' . $this->getDatatype() . '/';
    }

    public function routePost()
    {
        \Route::any($this->action(), function () {
            return $this->processPost();
        });
    }

    public function routeOk()
    {
        $url = $this->redirectUrl();
        if ($url) {
            \Route::get($url, function () {
                return $this->renderOk();
            });
        }
    }

    public function renderOk()
    {
        $template = $this->findView('ok', 'forms ~ ok');
        return view($template, ['form' => $this]);
    }

    public function automaticRoutes()
    {
        $this->routePost();
        $this->routeOk();
    }

    public function ajax()
    {
        return false;
    }

    public function defaultFormContext()
    {
        return [
            'form' => $this,
            'ajax' => $this->ajax(),
        ];
    }

    public function validateArgs($arg1 = false, $arg2 = false)
    {
        $template = $this->findView('form', 'forms ~ form');
        if (is_string($arg1)) {
            $template = $arg1;
        }
        $context = $this->defaultFormContext();
        if (is_array($arg1)) {
            $context = array_merge($context, $arg1);
        } elseif (is_array($arg2)) {
            $context = array_merge($context, $arg2);
        }
        return [$template, $context];
    }

    public function defaultAjaxOptions()
    {
        return [
            'show_loader' => true,
            'show_errors' => true,
        ];
    }

    public function ajaxOptions($context)
    {
        $options = $this->defaultAjaxOptions();
        foreach ($context as $k => $v) {
            if (is_string($v) || is_int($v) || is_bool($v)) {
                $options[$k] = $v;
            }
        }
        return $options;
    }

    public function renderForm($arg1 = false, $arg2 = false)
    {
        list($template, $context) = $this->validateArgs($arg1, $arg2);
        $info = [$arg1, $arg2];
        $key = 'tao_form_' . $this->getDatatype() . '_' . md5(serialize($info));
        \Session::put($key, $info);
        $context['session_key'] = $key;
        $context['ajax_options'] = json_encode($this->ajaxOptions($context));
        return view($template, $context);
    }

    public function validateForPublic()
    {
        return $this->validate();
    }

    public function ajaxError($errors, $context = [])
    {
        return [
            'errors' => $errors,
        ];
    }

    public function ajaxOk($context = [])
    {
        return [
            'result' => 'ok',
            'ok_message' => 'ok',
            'redirect' => false,
        ];
    }

    public function processPost()
    {
        $request = app()->request;
        $info = [false, false];
        $key = $request->get('_session_key');
        if (!empty($key)) {
            $info = \Session::get($key, $info);
        }

        list($template, $context) = $this->validateArgs($info[0], $info[1]);

        $fields = $this->publicFields();
        $errors = [];


        if ($request->method() == 'POST') {
            foreach ($fields as $field) {
                $field->setFromRequest($request);
            }
            $this->validateForPublic();
            $errors = $this->errors();
            if (!is_array($errors)) {
                $errors = array();
            }
            if (count($errors) == 0) {
                if (!empty($key)) {
                    $info = \Session::forget($key);
                }
                $this->save();
                foreach ($fields as $field) {
                    $field->setFromRequestAfterSave($request);
                }
                if ($request->ajax()) {
                    return response($this->ajaxOk($context));
                } else {
                    $url = $this->redirectUrl($context);
                    $url = empty($url) ? '/' : $url;
                    return redirect($url);
                }
            } else {
                if ($request->ajax()) {
                    return response($this->ajaxError($errors, $context));
                } else {
                    $context['form'] = $this;
                    $context['errors'] = $errors;
                    $econtext['errors'] = $errors;
                    $econtext['form_template'] = $template;
                    $econtext['form_context'] = $context;
                    $econtext['form'] = $this;
                    $etemplate = $this->findView('error', 'forms ~ error');
                    return view($etemplate, $econtext);
                }
            }
        }
    }
}
