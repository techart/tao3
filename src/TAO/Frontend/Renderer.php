<?php

namespace TAO\Frontend;

class Renderer extends \Techart\Frontend\Templates\Renderer
{
    public function render($name, $params = array())
    {
        $params = $this->defaultParams($name, $params);
        $dir = rtrim($this->src, '/');
        $name = trim($name, '/');
        $block = $this->blockName($name);
        $path = "{$dir}/src/block/{$name}/{$block}";
        $path = str_replace('/', '#', $path);
        return view($path, $params);
    }

    protected function defaultParams($path, $params)
    {
        $params = parent::defaultParams($path, $params);
        $params['renderer'] = $this;
        return $params;
    }

}