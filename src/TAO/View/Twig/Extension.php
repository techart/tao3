<?php

namespace TAO\View\Twig;

use TAO\View;
use TAO\View\Sections;

class Extension extends \Twig_Extension
{

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('navigation', [app('tao.view'), 'navigation'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('meta', [app('tao.view'), 'meta'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('set_meta', [app('tao.view'), 'setMeta'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('has_section', [app('tao.view'), 'hasSection'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('yield', [app('tao.view'), 'yieldSection'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('no_layout', [app('tao.view'), 'noLayout'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('within_layout', [app('tao.view'), 'withinLayout'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('use_style', [app('tao.assets'), 'useStyle'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('use_script', [app('tao.assets'), 'useScript'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('use_bottom_script', [app('tao.assets'), 'useBottomScript'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('assets', [app('tao.assets'), 'block'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('assets_styles', [app('tao.assets'), 'blockStyles'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('assets_scripts', [app('tao.assets'), 'blockScripts'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('assets_bottom_scripts', [app('tao.assets'), 'blockBottomScripts'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('view', [$this, 'render'], ['is_safe' => ['html'], 'needs_context' => true]),
		);
	}

	public function getTokenParsers()
	{
		return array(
			new SectionParser(),
		);
	}

	public function render($context, $template, $data = array())
	{
		$context = array_merge($context, $data);
		return app('tao.view')->render($template, $context);
	}
}