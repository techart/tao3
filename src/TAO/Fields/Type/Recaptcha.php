<?php

namespace TAO\Fields\Type;

use GuzzleHttp\Client;
use TAO\Fields\Field;

class Recaptcha extends Field
{
	const API_URL = 'https://www.google.com/recaptcha/api.js';
	const VALIDATE_URL = 'https://www.google.com/recaptcha/api/siteverify';
	const POST_PARAM_NAME = 'g-recaptcha-response';

	protected $httpClient;

	protected function isStorable()
	{
		return false;
	}

	public function renderPublicInput($arg1 = false, $arg2 = false)
	{
		$this->useScripts();
		return parent::renderPublicInput($arg1, $arg2);
	}

	public function renderInput($arg1 = false, $arg2 = false)
	{
		$this->useScripts();
		return parent::renderInput($arg1, $arg2);
	}

	public function validate($context = null)
	{
		/** @var \GuzzleHttp\Psr7\Response $response */
		$response = $this->httpClient()->request('POST', self::VALIDATE_URL, [
			'form_params' => [
				'secret' => $this->apiSecret(),
				'response' => \Request::input(self::POST_PARAM_NAME, ''),
				'remoteip' => \Request::ip()
			]
		]);
		if ($response->getStatusCode() == 200) {
			$response = json_decode($response->getBody());
			if (!$response->success) {
				return __('fields.errors.recaptcha');
			}
		}
		return parent::validate($context);
	}

	protected function httpClient()
	{
		if (is_null($this->httpClient)) {
			$this->httpClient = new Client();
		}
		return $this->httpClient;
	}

	public function apiKey()
	{
		return $this->data['api_key'];
	}

	public function apiSecret()
	{
		return $this->data['api_secret'];
	}

	protected function useScripts()
	{
		\Assets::useScript(self::API_URL);
	}

	public function inAdminList()
	{
		return false;
	}

	public function inCSV()
	{
		return false;
	}

	public function renderForAdminView()
	{
		return '';
	}
}
