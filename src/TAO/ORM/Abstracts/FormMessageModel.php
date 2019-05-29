<?php

namespace TAO\ORM\Abstracts;

abstract class FormMessageModel extends \TAO\ORM\Model
{
	protected $fieldsMode = 'public_form';

	protected $formTitle = '';

	protected $sendNotify = true;
	protected $notifyEmail = false;
	protected $notifySubject = 'Заполнена форма';
	protected $notifyMailable = \TAO\Mail\FormMessageNotifyMail::class;
	protected $notifyHtmlTemplate = 'email.form.html.notify';
	protected $notifyTextTemplate = 'email.form.plain.notify';

	protected $sendReply = true;
	protected $replyEmail = false;
	protected $replySubject = 'Вы заполнили форму';
	protected $replyMailable = \TAO\Mail\FormMessageReplyMail::class;
	protected $replyHtmlTemplate = 'email.form.html.reply';
	protected $replyTextTemplate = 'email.form.plain.reply';


	/**
	 * @return array
	 */
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

	/**
	 * @return array
	 */
	public function publicFields()
	{
		$fields = [];
		foreach ($this->processedFields() as $field => $data) {
			if (!isset($data['protected']) || !$data['protected']) {
				$fields[$field] = $this->field($field);
			}
		}
		return $fields;
	}

	/**
	 * @return string
	 */
	public function adminMenuSection()
	{
		return 'Формы';
	}

	/**
	 * @return string
	 */
	public function htmlId($context = [])
	{
		return 'tao-form-' . $this->getDatatype();
	}

	/**
	 * @return string
	 */
	public function formClass($context = [])
	{
		return 'tao-form tao-form-' . $this->getDatatype();
	}

	/**
	 * @return string
	 */
	public function formMethod() {
		return 'post';
	}

	/**
	 * @return string
	 */
	public function formEnctype() {
		if ($this->isMultipartEnctypeRequired()) {
			return 'multipart/form-data';
		}

		return 'application/x-www-form-urlencoded';
	}

	public function isMultipartEnctypeRequired() {
		$isMultipartEnctypeRequired = false;

		foreach ($this->fieldsObjects() as $field) {
			if ($field->isMultipartEnctypeRequired()) {
				$isMultipartEnctypeRequired = true;
				break;
			}
		}

		return $isMultipartEnctypeRequired;
	}

	/**
	 * Настройки кнопки отправки
	 *
	 * @return array
	 */
	public function submitButtonSettings()
	{
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
	public function labelClass()
	{
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

	public function templateField($fieldName, $context = [])
	{
		$field = $this->fieldsObjects()[$fieldName];
		if ($field->param('type_in_form') === 'hidden') {
			return $this->findView("field-{$fieldName}", 'forms ~ hidden');
		}
		return $this->findView("field-{$fieldName}", 'forms ~ field');
	}

	public function renderInput($field, $forceType = false)
	{
		return $this->field($field, $forceType)->renderPublicInput();
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
		$template = '';
		$context = [];
		if (is_string($arg1)) {
			$template = $arg1;
		} else if (is_array($arg1)) {
			$context = $arg1;
		} else if (is_array($arg2)) {
			$context = $arg2;
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
		if ($this->isDatatype()) {
			return $this->newInstance()->renderForm($arg1, $arg2);
		}

		list($template, $context) = $this->validateArgs($arg1, $arg2);

		if (empty($template)) {
			$template = $this->findView('form', 'forms ~ form');
		}
		$context = \TAO::merge($this->defaultFormContext(), $context);

		$info = [$arg1, $arg2];
		$key = uniqid('tao_form_');
		\Session::put($key, $info);
		$context['session_key'] = $key;
		$context['ajax_options'] = json_encode($this->ajaxOptions($context));
		$beforeRenderResult = $this->beforeRenderForm($context, $template);
		if (is_array($beforeRenderResult)) {
			$context = \TAO::merge($context, $beforeRenderResult);
		} elseif (is_string($beforeRenderResult)) {
			return $beforeRenderResult;
		}
		return view($template, $context);
	}

	/**
	 * Хук перед рендером формы. Можно модифицировать данные, передаваемые в шаблон или отрендерить форму
	 *
	 * Если возвращает массив, то он мержится с данными, передаваемыми в шаблон
	 *
	 * @param $context - данные, передаваемые в шаблон
	 * @param $template - имя шаблона
	 *
	 * @return string|array|void
	 */
	protected function beforeRenderForm($context, $template)
	{
	}

	public function validateForPublic()
	{
		return $this->validate('public');
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
		$item = $this->newInstance();
		$fields = $item->publicFields();
		if ($request->method() == 'POST') {
			foreach ($fields as $field) {
				$field->setFromRequest($request);
			}
			$item->validateForPublic();
			$errors = $item->errors();
			if (!is_array($errors)) {
				$errors = array();
			}
			if (count($errors) == 0) {
				$item->save();
				foreach ($fields as $field) {
					$field->setFromRequestAfterSave($request);
				}
				if (!is_null($afterInsertResult = $item->afterMessageInsert())) {
					return $afterInsertResult;
				}
				if ($request->ajax()) {
					return response($item->ajaxOk($context));
				} else {
					$url = $item->redirectUrl($context);
					$url = empty($url) ? '/' : $url;
					return redirect($url);
				}
			} else {
				if ($request->ajax()) {
					return response($item->ajaxError($errors, $context));
				} else {
					$context['form'] = $item;
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

	public function getFormTitle()
	{
		if ($this->formTitle) {
			return $this->formTitle;
		}
		return $this->getDatatype();
	}

	protected function getNotifyList()
	{
		if (!$this->notifyEmail) {
			return false;
		}
		return array_wrap($this->notifyEmail);
	}

	protected function getNotifySubject()
	{
		return $this->notifySubject;
	}

	protected function getReplyList()
	{
		if (!$this->replyEmail) {
			return false;
		}
		return array_wrap($this->replyEmail);
	}

	protected function getReplySubject()
	{
		return $this->replySubject;
	}

	protected function afterMessageInsert()
	{
		if ($this->sendNotify && $this->notifyMailable &&
			($notifyList = $this->getNotifyList()) &&
			($message = new $this->notifyMailable($this, $this->getNotifySubject(), $this->notifyHtmlTemplate, $this->notifyTextTemplate))) {
			foreach ($notifyList as $contact) {
				\Mail::to($contact)->send($message);
			}
		}

		if ($this->sendReply && $this->replyMailable &&
			($replyList = $this->getReplyList()) &&
			($message = new $this->replyMailable($this, $this->getReplySubject(), $this->replyHtmlTemplate, $this->replyTextTemplate))) {
			foreach ($replyList as $contact) {
				\Mail::to($contact)->send($message);
			}
		}
	}
}
