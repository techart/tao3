<?php
namespace TAO\Components\Sitemap;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

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
	/**
	 * @var array
	 */
	protected $extraLinks = [];

	protected $cacheName = 'laravel.sitemap';

	/**
	 * Динамически генерирует контент sitemap.xml
	 *
	 * @param int $cacheLifeTime
	 * @param string $rootUrl
	 * @return Response
	 */
	public function generateDynamically($cacheLifeTime = 0, $rootUrl = '')
	{
		if ($rootUrl != '') {
			\URL::forceRootUrl($rootUrl);
		}
		$this->registerDefaultSources();
		return $this->render($cacheLifeTime);
	}

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
		$this->registerSourcesFromConfig();
		$this->registerDatatypes();
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
		$links = $this->extraLinks;
		foreach ($this->sources as $source) {
			$links = array_merge($links, $this->makeSource($source)->sitemapLinks());
		}
		return array_map([$this, 'normalizeLink'], $links);
	}

	/**
	 * Генерирует контент файла sitemap.
	 *
	 * @param int $cacheLifetime
	 * @return Response
	 */
	public function render($cacheLifetime = 0)
	{
		if ($cacheLifetime && $this->checkCache($this->cacheName())) {
			$value = $this->getFromCache($this->cacheName());
			return $this->getResponse($value);
		}
		$sitemap = $this->makeSitemap();
		$value = $this->addLinksToSitemap($sitemap, $this->collectLinks())->toResponse(request());
		if ($cacheLifetime) {
			Cache::put($this->cacheName(), $value->original, $cacheLifetime);
		}
		return $this->addLinksToSitemap($sitemap, $this->collectLinks())->toResponse(request());
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
		$sitemap = $this->makeSitemap();
		$links = $this->collectLinks();
		if ($this->isRequiredSeparatedSitemap($urlsLimit, $links)) {
			$sitemapIndex = $this->makeSitemapIndex();
			$this->addLinksToSitemapIndexForSeparatedSitemap($sitemapIndex, $links, $urlsLimit);
			$sitemapIndex
				->writeToFile(public_path('sitemap.xml'));
		} else {
			$this->addLinksToSitemap($sitemap, $links)
				->writeToFile(public_path('sitemap.xml'));
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
		$link['loc'] = url($link['loc']);
		if (isset($link['changefreq'])) {
			$link['freq'] = $link['changefreq'];
		}
		return $this->linkToObject($link);
	}

	public function addLinks($links)
	{
		$this->extraLinks = array_replace_recursive($this->extraLinks, $links);
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
	protected function addLinksToSitemap($generator, $links)
	{
		foreach ($links as $linkData) {
			$generator->add($linkData);
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
	protected function addLinksToSitemapIndexForSeparatedSitemap($sitemapIndex, $links, $urlsLimit)
	{
		foreach (array_chunk($links, $urlsLimit) as $chunkIndex => $linksChunk) {
			$this->storeSitemapChunk($linksChunk, $sitemapIndex, $chunkIndex);
		}
		return $sitemapIndex;
	}

	/**
	 * Создает файл sitemap с ссылками $links и именем $name, используя переданный генератор.
	 *
	 * @param [] $links
	 * @param Sitemap $generator
	 * @param string $name
	 * @return string
	 */
	protected function storeLinksWithGenerator($links, $sitemap, $name)
	{
		foreach ($links as $linkData) {
			$sitemap->add($linkData);
		}
		return $sitemap->writeToFile(public_path($name . '.xml'));
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
		$sitemap = $this->makeSitemap();
		$generator->add('/' . $sitemapName);
		$this->storeLinksWithGenerator($links, $sitemap, $sitemapName);
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
	 * Создает экземпляр sitemap.
	 *
	 * @return Sitemap
	 */
	protected function makeSitemap()
	{
		return Sitemap::create();
	}


	/**
	 * Создает экземпляр SitemapIndex
	 * 
	 * @return SitemapIndex
	 */
	protected function makeSitemapIndex()
	{
		return SitemapIndex::create();
	}


	/**
	 * Проверяет есть ли значение по ключю в кэше
	 *
	 * @return boolean
	 */
	protected function checkCache($key)
	{
		return Cache::has($key);
	}

	/**
	 * Возвращает значение из кэша
	 *
	 * @param string $key ключ по которому значение хранится в кэше
	 * @return string|null
	 */
	protected function getFromCache($key)
	{
		return Cache::get($key);
	}

	/**
	 * Создает response на основе значения сайтмапа
	 *
	 * @param String $value строка сайтмапа
	 * @return Request
	 */
	protected function getResponse($value)
	{
		return response($value, 200, ['Content-type' => 'text/xml; charset=utf-8']);
	}

	/**
	 *
	 * Оборачивает нормализованную ссылку в обьект для генератора сайтмапа
	 *
	 * @param array $link Нормализованный массив с параметрами ссылки
	 * @return URL
	 */
	protected function linkToObject($link)
	{
		$linkObj = URL::create($link['loc']);
		$linkObj->setLastModificationDate($link['lastmod'] ?? Carbon::now());
		$linkObj->setPriority($link['priority'] ?? 0);
		$linkObj->setChangeFrequency($link['freq'] ?? '');
		return $linkObj;
	}
}
