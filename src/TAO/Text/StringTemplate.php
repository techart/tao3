<?php

namespace TAO\Text;

use TAO\Exception;

/**
 * Class StringTemplate
 *
 * Класс предоставляет функционал биндинга данных в простые строковые шаблоны. Часто требуется для
 * генерации имен и путей файлов, мет по шаблонам.
 *
 * @property string $text
 * @property array $binds
 * @property string $regexpFlags
 * @property string $startVarDelimiter
 * @property string $endVarDelimiter
 *
 * @package TAO\Text
 */
class StringTemplate
{
    protected $template;
    protected $binds = [];

    protected $regexpFlags = 'U';
    protected $startVarDelimiter = '{';
    protected $endVarDelimiter = '}';

    /**
     * StringTemplate constructor.
     * @param string $text
     * @param array $binds
     */
    public function __construct($text, $binds = [])
    {
        $this->text = $text;
        if (!empty($binds)) {
            $this->bind($binds);
        }
    }

    /**
     * @param string $template
     * @param array $binds
     * @param null $delimiters
     * @param null $flags
     * @return StringTemplate
     */
    public static function process($template, $binds = [], $delimiters = null, $flags = null)
    {
        $obj = new self($template, $binds);
        if (!is_null($delimiters)) {
            $obj->configureDelimiters($delimiters);
        }
        if (!is_null($flags)) {
            $obj->configureRegexpFlags($flags);
        }
        return $obj;
    }

    /**
     * @param array $binds
     */
    public function bind($binds)
    {
        $this->binds = array_replace($this->binds, $binds);
    }

    /**
     * @return string
     */
    public function asString()
    {
        return preg_replace($this->regexps(), $this->binds, $this->text);
    }

    public function __toString()
    {
        return $this->asString();
    }

    /**
     * @return array
     */
    protected function regexps()
    {
        $regexps = [];
        foreach (array_keys($this->binds) as $bindName) {
            $regexps[] = $this->makeRegexp($bindName);
        }
        return $regexps;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function makeRegexp($name)
    {
        return '~' . $this->startVarDelimiter . $name . $this->endVarDelimiter . '~' . $this->regexpFlags;
    }

    /**
     * @param string|array $delimiters
     * @throws Exception
     */
    public function configureDelimiters($delimiters)
    {
        if (is_string($delimiters)) {
            $this->startVarDelimiter = $this->endVarDelimiter = $delimiters;
        } else if (is_array($delimiters) && count($delimiters) == 2) {
            $this->startVarDelimiter = $delimiters[0];
            $this->endVarDelimiter = $delimiters[1];
        } else {
            throw new Exception('Incorrect delimiters for StringTemplates: expected string or array with two element');
        }
    }

    /**
     * @param string $flags
     */
    public function configureRegexpFlags($flags)
    {
        $this->regexpFlags = $flags;
    }
}
