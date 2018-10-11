<?php

namespace TAO\ORM\Traits;

use TAO\Fields\Field;
use TAO\Fields\Model;
use TAO\Fields\PageModel;
use TAO\Foundation\Request;

/**
 * Trait Addressable
 *
 * Трейт для моделей, могущих откликаться по урлу, заданному в админке, а также по дефолтному урлу
 *
 * @method Field field($name, $forceType = false)
 * @method static $this where(string | array | \Closure $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 */
trait Addressable
{
	public function initExtraAddressable()
	{
		$this->extraFields = \TAO::merge($this->extraFields, [
			'url' => array(
				'type' => 'string(250) index',
				'label' => 'URL',
				'style' => 'width:70%;',
				'weight' => -800,
				'in_list' => false,
				'in_form' => true,
				'group' => 'common',
			),
		]);
	}

	/**
	 * Возвращает урл записи. Если не задан в админке, то возвращается дефолтный
	 *
	 * @return string
	 */
	public function url($mode = 'full')
	{
		$url = trim($this->field('url')->value());
		if ($url == '') {
			return $this->defaultUrl($this, $mode);
		}
		return $url;
	}

	/**
	 * Возвращает запись по урлу (заданному в админке)
	 *
	 * @param $url
	 * @return mixed
	 */
	public function getItemByUrl($url)
	{
		return $this->where('url', $url)->first();
	}

	/**
	 * Дефолтный урл итема (если урл не задан в админке)
	 * В качестве параметра может передаваться как итем, так и id
	 *
	 * @param Model|string $item
	 * @return string
	 */
	public function defaultUrl($item, $mode = 'full')
	{
		$id = is_object($item) ? $item->getKey() : $item;
		$url = '/' . $this->getDatatype() . "/{$id}/";
		return $url;
	}

	/**
	 * Роутинг дататайпа по урлу, заданному в админке
	 *
	 * $data - параметры роутинга: (в примере рассматриваем урл /russia/moscow/)
	 * - finder:    имя метода, который будет искать итем по урлу (по умолчанию - getItemByUrl)
	 * - pages:     если задан, то урл может быть многостраничным (/russia/moscow/page-1/ и т.д.). Номер страницы передается в Model::renderItemPage
	 * - prefix:    префикс урла. Например - если задан news, то сработает /news/russia/moscow/
	 * - postfix:   постфикс урла. Например - если задан shops, то сработает /russia/moscow/shops/
	 * - mode:      режим отображения (по умолчанию - full)
	 *
	 * @param array $data
	 * @return $this
	 */
	public function routePageByUrl($data = [])
	{
		if (\TAO::isCLI()) {
			return $this;
		}
		/**
		 * @var Request $request
		 */
		$request = app()->request();
		$url = $urlSrc = $request->getPathInfo();

		if (isset($data['pages']) || isset($data['listing'])) {
			if ($m = \TAO::regexp("{^(.+)/page-(\d+)/$}", $url)) {
				$url = $m[1] . '/';
				$data['page'] = $page = (int)$m[2];
			}
		}

		if (isset($data['prefix'])) {
			$prefix = trim($data['prefix'], '/');
			if ($m = \TAO::regexp("{^/{$prefix}/(.+)$}", $url)) {
				$url = '/' . $m[1];
			} else {
				return $this;
			}
		}

		if (isset($data['postfix'])) {
			$postfix = trim($data['postfix'], '/');
			if ($m = \TAO::regexp("{^(.+)/{$postfix}/$}", $url)) {
				$url = $m[1] . '/';
			} else {
				return $this;
			}
		}

		$finder = isset($data['finder']) ? $data['finder'] : 'getItemByUrl';
		$mode = isset($data['mode']) ? $data['mode'] : 'full';
		$item = $this->$finder($url);
		if ($item instanceof \Illuminate\Database\Eloquent\Builder) {
			$item = $item->first();
		}
		if ($item) {
			$data['item'] = $item;
			$data['mode'] = $mode;
			\Route::any($urlSrc, function () use ($item, $data) {
				/**
				 * @var PageModel $item
				 * @var array $data
				 */
				if ($item->accessView(\Auth::user())) {
					$response = $this->renderItemPage($data);
				} else {
					$response = \TAO::pageNotFound();
				}
				return $response;
			});
		}
		return $this;
	}


	/**
	 * Роутинг по дефолтному урлу. Если итем найден по дефолтному урлу, а у него в админке задан другой урл, то произойдет редирект 301
	 *
	 * @return $this
	 */
	public function routePageById($data = [])
	{
		$mode = isset($data['mode']) ? $data['mode'] : 'full';
		$url = isset($data['url']) ? $data['url'] : $this->defaultUrl('{id}', $mode);

		\Route::any($url, function ($id) use ($mode, $data) {
			/** @var PageModel $item */
			$item = $this->getItemById($id);
			if (!$item || !$item->accessView(\Auth::user())) {
				return \TAO::pageNotFound();
			}

			$redirect = isset($data['redirect_to_valid_url']) ? $data['redirect_to_valid_url'] : false;

			if ($redirect) {
				$itemUrl = $item->url($mode);
				/** @var Request $request */
				$request = app()->request();
				$url = $request->getPathInfo();
				if ($url != $itemUrl) {
					return \Redirect::away($itemUrl, 301);
				}
			}

			$data['item'] = $item;
			$data['mode'] = $mode;
			return $this->renderItemPage($data);
		})->where('id', '^\d+$');

		return $this;
	}
}