<?php

namespace TAO;

use Illuminate\Contracts\View\Factory as ViewFactory;
use TAO\View\Sections;

/**
 * Class View
 * @package TAO
 */
class View
{
	/**
	 *
	 */
	public function init()
	{
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function navigation($name = 'site')
	{
		return \TAO::navigation($name);
	}

	/**
	 * @return mixed
	 */
	public function meta()
	{
		return \TAO::meta();
	}

	/**
	 * @param $meta
	 * @param $value
	 * @return mixed
	 */
	public function setMeta($meta, $value)
	{
		return \TAO::setMeta($meta, $value);
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function hasSection($name)
	{
		return Sections::has($name);
	}

	/**
	 * @param $name
	 * @return string
	 */
	public function yieldSection($name)
	{
		return Sections::get($name);
	}

	/**
	 *
	 */
	public function noLayout()
	{
		\TAO::useLayout(false);
	}

	/**
	 * @param $layout
	 */
	public function withinLayout($layout)
	{
		\TAO::useLayout($layout);
	}

	/**
	 * @param $template
	 * @param $context
	 * @return array
	 */
	public function renderSections($template, $context)
	{
		$r = view($template, $context);
		$sections = array();
		foreach ($r->renderSections() as $section => $content) {
			$sections[$section] = $content;
			Sections::set($section, $content);
		}
		$sections['%'] = $r->render();
		return $sections;
	}

	/**
	 * @param $template
	 * @param $context
	 * @return mixed
	 */
	public function render($template, $context)
	{
		$sections = $this->renderSections($template, $context);
		$content = $sections['%'];
		return $content;
	}

	/**
	 * @param $template
	 * @param $context
	 * @return mixed
	 */
	public function renderWithinLayout($template, $context)
	{
		$sections = $this->renderSections($template, $context);
		$content = $sections['%'];
		$layout = \TAO::getLayout();
		if ($layout) {
			if (!isset($sections['content'])) {
				Sections::set('content', $content);
			}
			$factory = app(ViewFactory::class);
			foreach (Sections::all() as $section => $sectionContent) {
				$factory->startSection($section);
				print $sectionContent;
				$factory->stopSection();
			}
			$r = $factory->make($layout, $context);
			$content = $r->render();
		}
		return $content;
	}
}
