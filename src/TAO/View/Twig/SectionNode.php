<?php

namespace TAO\View\Twig;

class SectionNode extends \Twig_Node
{
    protected $sectionName;

    public function __construct($name, $content, $lineno)
    {
        parent::__construct(['content' => $content], [], $lineno, 'section');
        $this->sectionName = $name;
    }

    /**
     * @param \Twig_Compiler $compiler
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $content = $this->getNode('content');
        $compiler
            ->addDebugInfo($this)
            ->write('ob_start();')
            ->subcompile($content)
            ->write('$content = ob_get_clean();')
            ->write('\\TAO\\View\\Sections::set("'.$this->sectionName.'", $content);');
    }
}
