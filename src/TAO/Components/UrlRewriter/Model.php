<?php

namespace TAO\Components\UrlRewriter;

use function GuzzleHttp\Psr7\parse_query;
use function GuzzleHttp\Psr7\parse_request;

class Model extends \TAO\ORM\Model
{
	protected static $replaces;

	public $table = 'urlrewriter';
	public $adminMenuSection = '*site';
	public $typeTitle = 'Адреса';

	public function fields()
	{
		return array(
			'isactive' => array(
				'type' => 'checkbox index',
				'label' => 'Включено к показу',
				'label_in_admin_list' => 'Вкл',
				'default' => 1,
				'weight' => -900,
				'in_list' => true,
				'in_form' => true,
				'group' => 'common',
			),

			'url' => array(
				'type' => 'string(250) index',
				'label' => 'Исходный URL',
				'style' => 'width:70%;',
				'weight' => -700,
				'in_list' => true,
				'in_form' => true,
				'group' => 'common',
			),
			'action' => array(
				'type' => 'select',
				'label' => 'Действие',
				'items' => array(
					0 => 'Только параметры для страницы',
					301 => 'Редирект 301 на дополнительный URL',
					302 => 'Редирект 302 на дополнительный URL',
					1 => 'Полный синоним',
				),
				'style' => 'width:300px;',
				'weight' => -600,
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			),
			'url2' => array(
				'type' => 'string(250) index',
				'label' => 'Дополнительный URL',
				'style' => 'width:70%;',
				'weight' => -500,
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			),
			'meta_title' => array(
				'type' => 'string(250)',
				'label' => 'Title',
				'style' => 'width:90%;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.meta',
			),
			'meta_description' => array(
				'type' => 'text',
				'label' => 'Description',
				'style' => 'width:90%;height:50px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.meta',
			),
			'meta_keywords' => array(
				'type' => 'text',
				'label' => 'Keywords',
				'style' => 'width:90%;height:50px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.meta',
			),
			'h1' => array(
				'type' => 'string(250)',
				'label' => 'H1',
				'style' => 'width:90%;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.text',
			),
			'top' => array(
				'type' => 'text',
				'label' => 'Текст сверху',
				'style' => 'width:90%;height:100px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.text',
			),
			'bottom' => array(
				'type' => 'text',
				'label' => 'Текст снизу',
				'style' => 'width:90%;height:100px;',
				'in_list' => false,
				'in_form' => true,
				'group' => 'common.text',
			),
		);
	}

	public function adminFormGroups()
	{
		return array(
			'common' => 'Основные параметры',
			'common.meta' => 'SEO-информация',
			'common.text' => 'Отображаемая информация',
		);
	}


	public function beforeSave()
	{
		$this['url2'] = \TAO\Urls::sortUrl($this['url2']);
		$this['url'] = \TAO\Urls::sortUrl($this['url']);
	}

	public function checkable($request)
	{
		if ($request->method() != 'GET') {
			return false;
		}
		$path = $request->path();
		if ($path == 'admin' || strpos($path, 'admin') === 0) {
			return false;
		}
		return true;
	}

	public function modifyRequest($request)
	{
		if (!$this->checkable($request)) {
			return $request;
		}
		$url = $request->getPathInfo();
		$item = $this->where('url', $url)->where('isactive', 1)->first();
		if ($item) {
			\Assets::urlRewrited($item);
			if ($item->action == 1) {
				$uri = trim($item->url2);
				if (!empty($uri)) {
					$data = parse_url($uri);
					if (isset($data['path'])) {
						$path = $data['path'];
						$args = [];
						if (isset($data['query'])) {
							parse_str($data['query'], $args);
						}
						$args = \TAO::merge($args, $request->query->all());
						$qs = http_build_query($args);
						\Assets::urlRewrited($item, $request);
						$request->replaceUri($path, $args);
					}
				}
			} else {
				if ($item->action == 301 || $item->action == 302) {
					\Assets::needRedirect($item->url2, $item->action);
				} else {
					\Assets::urlRewrited($item);
				}
			}
		} else {
			$url = \TAO\Urls::sortUrl($request->getRequestUri());
			$item = $this->where('url2', $url)->where('isactive', 1)->first();
			if ($item && $item->action == 1) {
				\Assets::needRedirect($item->url, 301);
			}
		}
		return $request;
	}

	public function getReplaced($url)
	{
		if (!is_array(static::$replaces)) {
			static::$replaces = [];
			foreach ($this->where('action', 1)->get() as $row) {
				static::$replaces[$row->url2] = $row->url;
			}
		}
		return isset(static::$replaces[$url]) ? static::$replaces[$url] : $url;
	}

	public function filter()
	{
		return array_merge_recursive((parent::filter() ?: []), [
			'search' => [
				'type' => 'string(250)',
				'label' => '',
			],

			'type' => [
				'type' => 'select',
				'label' => 'Действие',
				'items' => [
					'none' => 'Не учитывать',
					'params' => 'Только параметры',
					'redirect' => 'Только редиректы',
				],
			]

		]);
	}

	public function applyFilterSearch($builder, $value)
	{
		return $builder->where('url', 'like', "%{$value}%")->orWhere('url2', 'like', "%{$value}%");
	}

	public function applyFilterType($builder, $value)
	{
		if($value == 'redirect') {
			return $builder->whereIn('action', [301, 302]);
		} elseif ($value == 'params') {
			return $builder->where('action', 0);
		}

	}
}
