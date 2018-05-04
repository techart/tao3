<?php

namespace TAO;
use TAO\Exception\InvalidCallbackParams;

/**
 * Класс предоставляет функционал использования своего синтаксиса для создания callback. Сейчас, помимо стандартного
 * синтаксиса http://php.net/manual/ru/language.types.callable.php предоставляет возможность вызвать метод
 * определенного datatype. Для этого нужно передать строку в формате:
 *
 * `datatype.{мнемокод_типа}::{название_метода}`
 *
 * Метод вызывается у созданного экземпляра класса, так что может быть нестатическим.
 *
 * Для callback'ов любого типа можно передать предопределенные аргументы массивом в следующем формате: первым
 * элементом валидный callback, а вторым - массив с параметрами. Примеры:
 *
 * - ['function', [$arg1, $arg2,...]]
 * - [[$obj, 'methodName'], [$arg1, $arg2,...]]
 * - ['datatype.page::method', [$arg1, $arg2,...]]
 *
  *
 * @package TAO
 */
class Callback
{
	protected $callback;
	protected $args;

	protected static $regexps = [
		'datatype' => '{^datatype.(.+?)::(.+)$}'
	];

	public static function isValidCallback($callback)
	{
		try {
			return self::instance($callback)->isValid();
		} catch (Exception $e) {
			return false;
		}
	}

	public function isValid()
	{
		return $this->callback instanceof Callback
			|| (is_string($this->callback) && preg_match(static::$regexps['datatype'], $this->callback))
			|| is_array($this->callback) && method_exists($this->callback[0], $this->callback[1])
			|| !is_array($this->callback) && is_callable($this->callback);
	}

	public static function instance($callback)
	{
		return $callback instanceof Callback ? $callback : new self($callback);
	}

	public function __construct($callback)
	{
		$this->parse($callback);
	}

	public function args($args)
	{
		$this->args = $args;
		return $this;
	}

	public function call()
	{
		return call_user_func_array($this->callback, $this->args ?: func_get_args());
	}

	protected function parse($callback)
	{
		$callback = $this->extractArguments($callback);
		if (is_string($callback) && preg_match(static::$regexps['datatype'], $callback, $m)) {
			$datatype = \TAO::datatype($m[1]);
			if (!$datatype) {
				throw new \InvalidCallbackParams("Unknown datatype {$datatype}");
			}
			$this->callback = [$datatype, $m[2]];
		} else if (is_callable($callback)) {
			$this->callback = $callback;
		} else {
			$callbackForMessage = is_string($callback) ? $callback : print_r($callback, true);
			throw new InvalidCallbackParams("Invalid callback {$callbackForMessage}");
		}
	}

	protected function extractArguments($callback)
	{
		if ($this->hasArguments($callback)) {
			$this->args($callback[1]);
			$callback = $callback[0];
		}
		return $callback;
	}

	protected function hasArguments($callback)
	{
		return is_array($callback) && count($callback) == 2 && self::isValidCallback(reset($callback));
	}
}