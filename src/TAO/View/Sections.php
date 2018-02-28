<?php

namespace TAO\View;

class Sections
{
	protected static $sections = '';

	public static function set($name, $content)
	{
		self::$sections[$name] = $content;
	}

	public static function has($name)
	{
		return isset(self::$sections[$name]);
	}

	public static function get($name, $default = '')
	{
		return self::has($name) ? self::$sections[$name] : $default;
	}

	public static function all()
	{
		return self::$sections;
	}
}