<?php
namespace TAO\Components\Sitemap;
use Roumen\Sitemap\Sitemap;
use TAO\Urls;

/**
 * Class Sitemap
 *
 */
class Manager
{
	/**
	 * @var string|SitemapSource[]
	 */
	protected $sources = [];

	protected $cacheName = 'laravel.sitemap';

	/**
	 * Регистрирует поставщика ссылок для sitemap. Должен реализовывать интерфейс SitemapSource.
	 * Допускается передача имени класса или готового объекта.
	 *
	 * @param string|SitemapSource $class
	 */
	public function registerSource($class)
	{
		$this->sources[] = $class;
	}

	/**
	 * Метод регистрирует все типы данных, которые используют trait Sitemap и у которых метод inSitemap
	 * возвращает true(в трейте по умолчанию).
	 */
	public function registerDatatypes()
	{
		foreach (\TAO::datatypes() as $datatype) {
			/** @var \TAO\ORM\Model|\TAO\ORM\Traits\Sitemap $datatype */
			if ($this->isDatatypeInSitemap($datatype)) {
				$this->registerSource($datatype);
			}
		}
	}

	/**
	 * Метод регистрирует поставщиков из конфига sitemap.sources
	 */
	public function registerSourcesFromConfig()
	{
		foreach (config('sitemap.sources', []) as $source) {
			$this->registerSource($source);
		}
	}

	/**
	 * Регистрирует дефолтных поставщиков ссылок.
	 */
	public function registerDefaultSources()
	{
		$this->registerDatatypes();
		$this->registerSourcesFromConfig();
	}

	/**
	 * Проверяет является ли тип данных поставщиком ссылок.
	 *
	 * @param $datatype
	 * @return bool
	 */
	protected function isDatatypeInSitemap($datatype)
	{
		return method_exists($datatype, 'inSitemap') && $datatype->inSitemap();
	}

	/**
	 * Собирает ссылки со всех зарегистрированных поставщиков. Возвращает массив ссылок.
	 *
	 * @return array
	 */
	protected function collectLinks()
	{
		$links = [];
		foreach ($this->sources as $source) {
			$links = array_merge($links, $this->makeSource($source)->sitemapLinks());
		}
		return array_map([$this, 'normalizeLink'], $links);
	}

	/**
	 * Генерирует контент файла sitemap.
	 *
	 * @param int $cacheLifetime
	 * @return \Illuminate\Support\Facades\View
	 */
	public function render($cacheLifetime = 0)
	{
		$generator = $this->makeGenerator($cacheLifetime);
		return $this->addLinksToGenerator($generator, $this->collectLinks())->render();
	}

	/**
	 * Генерирует sitemap и сохраняет его в файл sitemap.xml. Если количество зарегистрированных
	 * ссылок болше лимита, то разбивает sitemap на несколько файлов, в sitemap.xml в этом случае
	 * находится индексный sitemap.
	 *
	 * @param int $urlsLimit
	 */
	public function store($urlsLimit = 0)
	{
		$generator = $this->makeGenerator();
		$links = $this->collectLinks();
		if ($this->isRequiredSeparatedSitemap($urlsLimit, $links)) {
			$this->addLinksToGeneratorForSeparatedSitemap($generator, $links, $urlsLimit);
			return $generator->store('sitemapindex', 'sitemap');
		} else {
			return $this->addLinksToGenerator($generator, $links)->store();
		}
	}

	/**
	 * Возвращает экземпляр класса поставщика.
	 *
	 * @param string|SitemapSource $source
	 * @return SitemapSource
	 */
	protected function makeSource($source)
	{
		return is_string($source) ? app()->make($source) : $source;
	}

	/**
	 * Приводит ссылку к нужному формату:
	 * [
	 *  'loc' => ...,
	 *  'freq' => ...,
	 *  'priority' => ...,
	 *  'lastmod' => ...
	 * ]
	 *
	 * @param string|array $link
	 * @return array
	 */
	public function normalizeLink($link)
	{
		$link = is_string($link) ? ['loc' => $link] : $link;
		if (isset($link['changefreq'])) {
			$link['freq'] = $link['changefreq'];
		}
		return $link;
	}

	/**
	 * Название кеша, в котором хранится закешированный sitemap
	 *
	 * @return string
	 */
	protected function cacheName()
	{
		return $this->cacheName;
	}

	/**
	 * Добавляет ссылки в указанный генератор
	 *
	 * @param Sitemap $generator
	 * @param [] $links
	 * @return Sitemap
	 */
	protected function addLinksToGenerator($generator, $links)
	{
		foreach ($links as $linkData) {
			$generator->addItem($linkData);
		}
		return $generator;
	}

	/**
	 * Добавляет ссылки в указанный генератор, разбивая ссылки на части, соответсвующие переданному лимиту.
	 *
	 * @param Sitemap $generator
	 * @param [] $links
	 * @param int $urlsLimit
	 * @return Sitemap
	 */
	protected function addLinksToGeneratorForSeparatedSitemap($generator, $links, $urlsLimit)
	{
		foreach (array_chunk($links, $urlsLimit) as $chunkIndex => $linksChunk) {
			$this->storeSitemapChunk($linksChunk, $generator, $chunkIndex);
		}
		return $generator;
	}

	/**
	 * Создает файл sitemap с ссылками $links и именем $name, используя переданный генератор.
	 *
	 * @param [] $links
	 * @param Sitemap $generator
	 * @param string $name
	 * @return string
	 */
	protected function storeLinksWithGenerator($links, $generator, $name)
	{
		foreach ($links as $linkData) {
			$generator->addItem($linkData);
		}
		return $generator->store('xml', $name);
	}

	/**
	 * Создает файл sitemap с указанной частью ссылок и добавляет указанный файл в индексный.
	 *
	 * @param [] $links
	 * @param Sitemap $generator
	 * @param int|string $index
	 */
	protected function storeSitemapChunk($links, $generator, $index)
	{
		$sitemapName = 'sitemap' . $index;
		$this->storeLinksWithGenerator($links, $generator, $sitemapName);
		$generator->addSitemap(Urls::full_url($sitemapName . '.xml'));
		$generator->model->resetItems();
	}

	/**
	 * Проверяет требуется ли разделение sitemap на части.
	 *
	 * @param int $urlsLimit
	 * @param [] $links
	 * @return bool
	 */
	protected function isRequiredSeparatedSitemap($urlsLimit, $links)
	{
		return $urlsLimit > 0 && count($links) > $urlsLimit;
	}

	/**
	 * Создает экземпляр генератора sitemap.
	 *
	 * @param int $cacheLifetime
	 * @return \Illuminate\Foundation\Application|mixed|Sitemap
	 */
	protected function makeGenerator($cacheLifetime = 0)
	{
		$generator = app('sitemap');
		if ($cacheLifetime > 0) {
			$generator->setCache($this->cacheName(), $cacheLifetime);
		}
		return $generator;
	}
}