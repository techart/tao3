<?php

namespace TAO\Text;

use TAO\Callback;

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
	protected $text;
	protected $values = [];

	protected $regexp = '/\{(.+?)\}/';
	protected $regexpIndex = 1;

	/**
	 * StringTemplate constructor.
	 * @param string $text
	 * @param array $values
	 */
	public function __construct($text, $values = [])
	{
		$this->text = $text;
		if (!empty($values)) {
			$this->setValues($values);
		}
	}

	/**
	 * @param string $template
	 * @param array|callback $values
	 * @param string $regexp
	 * @param int $regexpIndex
	 * @return StringTemplate
	 */
	public static function process($template, $values = [], $regexp = null, $regexpIndex = null)
	{
		$obj = new self($template, $values);
		if (!is_null($regexp)) {
			$obj->setRegexp($regexp);
		}
		if (!is_null($regexpIndex)) {
			$obj->setRegexpIndex($regexpIndex);
		}
		return $obj->asString();
	}

	/**
	 * @param array $values
	 */
	public function setValues($values)
	{
		$this->values = $values;
	}

	/**
	 * @return string
	 */
	public function asString()
	{
		return preg_replace_callback($this->regexp, [$this, 'replaceVarCallback'], $this->text);
	}

	public function replaceVarCallback($matches)
	{
		if (isset($matches[$this->regexpIndex])) {
			$value = $this->getValue($matches[$this->regexpIndex]);
			if (!is_null($value)) {
				return $value;
			}
		}
		return $matches[0];
	}

	public function getValue($valueName)
	{
		$value = null;
		if (\TAO\Type::isCallable($this->values)) {
			$value = Callback::instance($this->values)->call($valueName);
		} else {
			if (isset($this->values[$valueName])) {
				$value = $this->values[$valueName];
			}
		}
		return $value;
	}

	public function __toString()
	{
		return $this->asString();
	}

	/**
	 * @param string $regexp
	 */
	public function setRegexp($regexp)
	{
		$this->regexp = $regexp;
	}

	/**
	 * @param int $index
	 */
	public function setRegexpIndex($index)
	{
		$this->regexpIndex = $index;
	}
}
