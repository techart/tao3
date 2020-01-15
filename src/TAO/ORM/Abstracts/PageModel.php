<?php

namespace TAO\ORM\Abstracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class PageModel
 * @package TAO\Fields
 *
 * Абстрактный класс для страничных моделей
 */
abstract class PageModel extends \TAO\ORM\Model
{
	use \TAO\ORM\Traits\Switchable,
		\TAO\ORM\Traits\Addressable,
		\TAO\ORM\Traits\Title,
		\TAO\ORM\Traits\Metas,
		\TAO\ORM\Traits\Sitemap,
		\TAO\ORM\Traits\CheckUniqueUrl;

	/**
	 * Шаблон урла. Если урл не задан, то при сохранении будет сгенерирован по шаблону.
	 * Вставки - {date}, {title}, {id}
	 */
	protected $urlTemplate = false;

	/**
	 *
	 */
	protected function initExtraFields()
	{
		$this->initExtra('Switchable', 'Addressable', 'Title', 'Metas');
	}

	/**
	 * @return array
	 */
	public function adminFormGroups()
	{
		return array(
			'common' => 'Основные параметры',
			'common.meta' => 'SEO-информация',
			'content' => 'Контент',
			'params' => 'Доп.параметры',
		);
	}

	/**
	 * Проверка прав доступа на просмотр страницы записи
	 *
	 * @param bool $user
	 * @return mixed
	 */
	public function accessView($user = false)
	{
		return $this->isactive;
	}

	/**
	 * @param array $data
	 * @return Builder
	 */
	public function getAccessibleItems($data = [])
	{
		return $this->ordered()->where('isactive', 1);
	}

	/**
	 * Строка даты для генерации урла по шаблону
	 */
	public function dateForUrl()
	{
		return empty($this->created_at) ? date('Y/m/d') : $this->created_at->format('Y/m/d');
	}

	/**
	 * Строка заголовка для генерации урла по шаблону
	 */
	public function titleForUrl()
	{
		return strtolower(\TAO\Text::process($this->title(), 'translit_for_url'));
	}

	/**
	 * Генерация урла (если не задан) по шаблону (если задан)
	 */
	protected function generateUrl()
	{
		if (!empty($this->url)) {
			return;
		}

		$url = $this->urlTemplate;
		if (!empty($url)) {
			$url = str_replace('{date}', $this->dateForUrl(), $url);
			$url = str_replace('{title}', $this->titleForUrl(), $url);
			$this->url = $url;
		}
	}

	/**
	 * Генерация урла (дополнительная часть, после сохранения записи)
	 */
	protected function generateUrlAfterSave()
	{
		if (empty($this->url)) {
			return;
		}

		$url = $this->url;
		if (strpos($url, '{id}') !== false) {
			$url = str_replace('{id}', $this->id, $url);
		}

		if ($url != $this->url) {
			$this->url = $url;
			$this->save();
		}
	}

    /**
     * @return bool
     *
     * Проверка урла на уникальность
     *
     */
	public function validateFieldUrl() {
		return method_exists($this, 'checkUniqueUrl') ? $this->checkUniqueUrl() : true;
    }

}
