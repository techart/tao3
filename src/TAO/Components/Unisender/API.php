<?php

namespace TAO\Components\Unisender;

use Unisender\ApiWrapper\UnisenderApi;

/**
 * Class API
 * @package TAO\Components\Unisender
 */
class API extends UnisenderApi
{
	/**
	 * @return API
	 */
	public static function makeInstance()
	{
		return new self(
			config('services.unisender.key'),
			config('services.unisender.encoding', 'UTF-8'),
			config('services.unisender.retry_count', 4),
			config('services.unisender.timeout'),
			config('services.unisender.compression', false),
			config('services.unisender.platform')
		);
	}

	/**
	 * @param $list
	 * @param $email
	 * @param bool $data
	 * @return string
	 */
	public function addEmail($email, $data = [])
	{
		$tags = false;
		$fields = [];
		$double = config('services.unisender.double_optin', 4);
		$overwrite = config('services.unisender.overwrite', 0);
		$tags = $data['tags'] ?? config('services.unisender.default_tags', false);
		$list = $data['list_id'] ?? $this->getDefaultListId();
		$fields = $data;
		unset($fields['tags']);
		if (isset($fields['double_optin'])) {
			$double = $fields['double_optin'];
			unset($fields['double_optin']);
		}
		if (isset($fields['overwrite'])) {
			$overwrite = $fields['overwrite'];
			unset($fields['overwrite']);
		}
		$fields['email'] = $email;
		$params = array(
			'list_ids' => $list,
			'fields' => $fields,
			'double_optin' => $double,
			'overwrite' => $overwrite,
		);
		if ($tags) {
			$params['tags'] = $tags;
		}
		return $this->subscribe($params);
	}

	/**
	 * @param bool $list
	 * @return mixed
	 */
	public function getContactCount($list = false)
	{
		if (is_array($list)) {
			return parent::getContactCount($list);
		}

		$data = array(
			'params' => array('type' => 'address'),
		);

		if (trim($list) != '') {
			$data['list_id'] = trim($list);
		} else {
			$data['list_id'] = $this->getListsIds();
		}
		return parent::getContactCount($data);
	}

	/**
	 * @param $offset
	 * @param $limit
	 * @return array|bool
	 */
	public function getAllContactsChunk($offset, $limit)
	{
		$rc = $this->exportContacts(array(
			'offset' => $offset,
			'limit' => $limit,
		));
		if (isset($rc->result)) {
			$result = $rc->result;
			if (isset($result->field_names) && isset($result->data)) {
				$out = [];
				$names = $result->field_names;
				foreach ($result->data as $row) {
					$item = new \StdClass;
					foreach ($names as $k => $name) {
						$item->{$name} = $row[$k];
					}
					$out[] = $item;
				}
				return $out;
			}
		}
		return false;
	}

	/**
	 * @return array
	 */
	public function getAllContacts()
	{
		$out = [];
		$offset = 0;
		$limit = 900;
		while (true) {
			$rows = $this->getAllContactsChunk($offset, $limit);
			$count = count($rows);

			if ($count == 0) {
				break;
			}

			$out = array_merge($out, $rows);

			if ($count < $limit) {
				break;

			}
			$offset += $limit;
		}
		return $out;
	}

	/**
	 * @return bool|string
	 */
	public function getListsIds()
	{
		if ($ids = $this->getListIdByName()) {
			return implode(',', $ids);
		}
		return false;
	}

	/**
	 * @param bool $code
	 * @return array|bool|mixed|null
	 */
	public function getListIdByName($code = false)
	{
		if ($result = $this->getLists()->result) {
			$ids = [];
			foreach ($result as $list) {
				$ids[$list->title] = $list->id;
			}
			if ($code) {
				return $ids[$code] ?? null;
			}
			return $ids;
		}
		return false;
	}

	/**
	 * @return array|bool|mixed|null|string
	 */
	public function getDefaultListId()
	{
		if ($list = trim(config('services.unisender.default_list'))) {
			if (!preg_match('{^\d+$}', $list)) {
				$list = $this->getListIdByName($list);
			}
			return $list;
		}
	}

	/**
	 * @param array $p1
	 * @param bool $p2
	 * @return int
	 */
	public function createEmailMessage($p1, $p2 = false)
	{
		if (is_array($p1)) {
			$params = $p1;
		} elseif (is_string($p1)) {
			if (is_string($p2)) {
				$subject = $p1;
				$body = $p2;
			} else {
				$body = $p1;
				$subject = config('services.unisender.default_subject');
			}
			if (preg_match('{^[a-z0-9\._-]+$}i', $body)) {
				$body = (string)view($body);
			}
			$params = array(
				'subject' => $subject,
				'body' => $body,
			);
		}
		$params['list_id'] = $params['list_id'] ?? $this->getDefaultListId();
		$params['sender_name'] = $params['sender_name'] ?? config('services.unisender.sender_name');
		$params['sender_email'] = $params['sender_email'] ?? config('services.unisender.sender_email');

		$result = parent::createEmailMessage($params);

		if (isset($result->result) && isset($result->result->message_id)) {
			return (int)$result->result->message_id;
		}

		if (isset($result->error)) {
			return $result->error;
		}

		return $result;
	}

	/**
	 * @param $p1
	 * @param bool $p2
	 * @return int
	 */
	public function sendEmailMessage($p1, $p2 = false)
	{
		$message = $this->createEmailMessage($p1, $p2);
		if (is_int($message)) {
			return $this->createCampaign(array(
				'message_id' => $message,
			));
		}
		return $message;
	}

	/**
	 * @param array $params
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function subscribeForm($params = [])
	{
		$params['time'] = time();
		$template = $params['template'] ?? 'unisender.form';
		$context = $params;
		$context['encrypted_data'] = \Crypt::encrypt(serialize($params));
		$context['submit_url'] = $params['submit_url'] ?? '/unisender/subscribe/';
		return view($template, $context);
	}

	public function routes()
	{
		\Route::post('/unisender/subscribe/', '\TAO\Components\Unisender\Controller@subscribe');
		\Route::get('/unisender/subscribe/ok/', '\TAO\Components\Unisender\Controller@ok');
	}

	/**
	 * @param string $methodName
	 * @param array $params
	 * @return mixed
	 */
	protected function callMethod($methodName, $params = [])
	{
		if (config('services.unisender.use_curl', false)) {
			$url = $this->getApiHost() . $methodName . '?format=json&api_key=' . $this->apiKey;
			$body = app('tao.http')->getBody($url, true, $params);
		} else {
			$body = parent::callMethod($methodName, $params);
		}
		return json_decode($body);
	}
}