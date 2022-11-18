<?php

namespace TAO\Fields\Type;

use Illuminate\Support\Facades\App;
use TAO\Fields\Type\Recaptcha;

class Yacaptcha extends Recaptcha
{
	//Ссылка на скрипт для рендера капчи
	public const API_URL = 'https://captcha-api.yandex.ru/captcha.js';

	//Обработка данных о пользователе
	public function getCaptchaData()
	{
		//Получаем токен капчи
		$token = $_POST['smart-token'];

		//Параметры 
		$args = http_build_query([
			"secret" => $this->apiSecret(),
			"token" => $token,
			"ip" => $_SERVER['REMOTE_ADDR']
		]);

		//Запрос к API Яндекса 
		$resp = app('tao.http')->get('https://captcha-api.yandex.ru/validate?' . $args);

		//Разрешает доступ из-за ошибки доступа к Яндекс API
		if ($resp->getStatusCode() !== 200) {
			return true;
		}

		//Возвращает ответ запроса
		return json_decode($resp->getResponse())->status === "ok";
	}

	//Получить язык капчи
	public function getLanguage()
	{
		return $this->param('locale', App::getLocale());
	}

	//Проверка и вывод ошибки
	public function validate($context = null)
	{
		//Если ответ не верный вывод ошибки
		if (!$this->getCaptchaData()) {
			//Получаем язык капчи
			$selectedLocale = $this->getLanguage();
			//Вывод ошибки
			return ($this->getErrorMsg($selectedLocale));
		}
	}

	//Получить сообщение ошибки с учетом языка
	public function getErrorMsg($locale)
	{
		return $this->param(['errormsg_' . $locale, 'errormsg'], __('fields.errors.yacaptcha'));
	}
}
