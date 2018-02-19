<?php

namespace TAO\Frontend;

use Techart\Frontend\PathResolver;

class Manager extends \Techart\Frontend\Frontend
{
    protected static $instances = [];

    public static function instanse($path = false, $options = [])
    {
        if (!is_string($path) || empty($path)) {
            $path = 'frontend';
        }
        if ($path[0] != '/' && $path[0] != '.') {
            $path = base_path($path);
        }
        $path = rtrim($path, '/');

        if (!isset(self::$instances[$path])) {
            $resolverOptions = array_merge(['twigCachePath' => "{$path}/twig"], $options);
            $resolver = new PathResolver($path, $resolverOptions);
            self::$instances[$path] = new self(Env::instanse(), $resolver);
        }
        return self::$instances[$path];
    }

    public function useStyle($name, $params = [])
    {
        $scope = isset($params['scope']) ? $params['scope'] : false;
        return \Assets::useFile($this->cssUrl($name), $scope);

    }

    public function useScript($name, $params = [])
    {
        $scope = isset($params['scope']) ? $params['scope'] : false;
        return \Assets::useFile($this->jsUrl($name), $scope);

    }

    public function repositoryInstance($factory)
    {
        return new Repository($factory);
    }

    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'render':
            case 'renderBlock':
                $obj = $this->templates();
                break;

            case 'url':
            case 'cssUrl':
            case 'jsUrl':
            case 'cssTag':
            case 'jsTag':
                $obj = $this->assets();
                break;
        }
        return call_user_func_array(array($obj, $name), $arguments);
    }
}