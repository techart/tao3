<?php
namespace TAO\Type;

class Collection
{
	/**
	 * @param $array
	 * @return bool
	 */
	public static function isIndexed($array)
	{
		return array_keys($array) === range(0, count($array) - 1) || empty($array);
	}

    /**
     * Обертка для parse_str
     *
     * @param mixed $in
     * @return mixed
     */
	public static function parseString($in)
    {
        if (is_string($in)) {
            parse_str($in, $out);
            return $out;
        }
        return $in;
    }

    /**
     * Фильтрует входной массив, оставляя только члены c цифровыми ключами
     *
     * @param mixed $in
     * @return array
     */
    public static function numericKeysOnly($in)
    {
        $out = [];
        $in = (array)$in;
        foreach ($in as $key => $value) {
            if (is_numeric($key)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}