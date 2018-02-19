<?php

namespace TAO\View\Twig;
use TAO\View;
use TAO\View\Sections;

class Extension extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('navigation', [app()->taoView, 'navigation'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('meta', [app()->taoView, 'meta'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('set_meta', [app()->taoView, 'setMeta'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('has_section', [app()->taoView, 'hasSection'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('yield', [app()->taoView, 'yieldSection'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('no_layout', [app()->taoView, 'noLayout'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('within_layout', [app()->taoView, 'withinLayout'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('use_style', [app()->taoAssets, 'useStyle'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('use_script', [app()->taoAssets, 'useScript'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('use_bottom_script', [app()->taoAssets, 'useBottomScript'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('assets', [app()->taoAssets, 'block'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('assets_styles', [app()->taoAssets, 'blockStyles'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('assets_scripts', [app()->taoAssets, 'blockScripts'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('assets_bottom_scripts', [app()->taoAssets, 'blockBottomScripts'], ['is_safe' => ['html']]),
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
        return app()->taoView->render($template, $context);
    }
}