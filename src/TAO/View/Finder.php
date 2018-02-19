<?php

namespace TAO\View;

use Illuminate\View\FileViewFinder;

class Finder extends FileViewFinder
{
    public function exists($view)
    {
        $path = false;
        try {
            $path = parent::find($view);
        } catch (\Exception $e) {
            return false;
        }
        return $path;
    }

    /**
     * @param string $name
     * @return string
     */
    public function find($name)
    {
        if ($name == '~layout') {
            $name = app()->tao->layout;
        }

        $name = preg_replace('{\s*~\s*}', '.', $name);

        if ($path = $this->exists($name)) {
            return $path;
        }

        foreach(\TAO::getViewsPrefixes() as $prefix) {
            if ($path = $this->exists("{$prefix}{$name}")) {
                return $path;
            }
        }

        if (is_string($name) && strpos($name, '#') !== false) {
            $path = str_replace('#', '/', $name);
            foreach (['phtml', 'blade.php', 'twig', 'html.twig'] as $ext) {
                if ($this->files->exists("{$path}.{$ext}")) {
                    if ($ext == 'phtml') {
                        \View::addExtension('phtml', 'php');
                    }
                    return "{$path}.{$ext}";
                }
            }
        }

        if ($path = $this->findInTAO($name)) {
            return $path;
        }

        return parent::find($name);
    }


    public function findInTAO($name)
    {
        $paths = \TAO::getViewsPaths();
        $paths['tao'] = true;

        $names = explode('|', $name);
        foreach ($names as $name) {
            if ($name = trim($name)) {
                if ($view = $this->exists($name)) {
                    return $view;
                }
                foreach ($paths as $vendor => $path) {
                    if ($view = $this->exists("{$vendor}::{$name}")) {
                        return $view;
                    }
                }
            }
        }
        return false;
    }

}