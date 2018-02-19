<?php

namespace TAO;

/**
 * Класс предоставляет функционал использования своего синтаксиса для создания callback. Сейчас, помимо стандартного
 * синтаксиса http://php.net/manual/ru/language.types.callable.php предоставляет возможность вызвать метод
 * определенного datatype. Для этого нужно передать строку в формате:
 *
 * `datatype.{мнемокод_типа}::{название_метода}`
 *
 * Метод вызывается у созданного экземпляра класса, так что может быть нестатическим.
 *
 * @package TAO
 */
class Callback
{
    protected $callback;

    protected static $regexps = [
        'datatype' => '{^datatype.(.+?)::(.+)$}'
    ];

    // TODO: изменить систему проверки на более универсальную
    public static function isValidCallback($callback)
    {
        return $callback instanceof Callback || (is_string($callback) && preg_match(static::$regexps['datatype'], $callback)) || is_callable($callback);
    }

    public static function instance($callback)
    {
        return $callback instanceof Callback ? $callback : new self($callback);
    }

    public function __construct($callback, $args = [])
    {
        $this->setCallback($callback);
    }

    public function call()
    {
        return call_user_func_array($this->callback, func_get_args());
    }

    protected function setCallback($callback)
    {
        $this->callback = $this->parse($callback);
    }

    protected function parse($callback)
    {
        if (is_string($callback) && preg_match(static::$regexps['datatype'], $callback, $m)) {
            $datatype = \TAO\Facades\TAO::datatype($m[1]);
            if (!$datatype) {
                throw new \InvalidCallbackParams("Unknown datatype {$datatype}");
            }
            $callback = [$datatype, $m[2]];
        } else if (is_callable($callback)) {
            $callback = $callback;
        } else {
            throw new \InvalidCallbackParams("Invalid callback {$callback}");
        }
        return $callback;
    }
}