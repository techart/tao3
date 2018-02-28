<?php

namespace TAO\Text\Processor;

use TAO\Text;
use TAO\Text\ConfigurableProcessorInterface;

/**
 * Class TranslitForUrl
 *
 * Основная область применения данного обработчика - транслит урлов и путей к файлам.
 *
 * Доступные параметры:
 * `delimiter` - символ, который будет заменять все нечисловиые и небуквенные символы в тексте
 * `max_length` - длина, по которой будет обрезаться результирующая строка
 * `case` - приведение к регистру. Значения - 'lower', 'upper', при остальных значениях обработка производиться не будет
 *
 * @package TAO\Text\Processor
 */
class TranslitForUrl implements ConfigurableProcessorInterface
{
	use Text\ProcessorOptions;

	/**
	 * @param string $text
	 * @param array $options
	 * @return string
	 */
	public function process($text, $options = [])
	{
		$this->initOptions($options);

		$text = $this->translit($text);
		$text = $this->replaceNonAlphanumericCharacters($text);
		$text = $this->replaceDoubleDelimiters($text);
		$text = $this->cutToMaxLength($text);
		$text = $this->applyCase($text);

		return $text;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function translit($text)
	{
		return Text::process($text, 'translit');
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function replaceNonAlphanumericCharacters($text)
	{
		return preg_replace('|[^a-zA-Z0-9]|', $this->option('delimiter'), $text);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function replaceDoubleDelimiters($text)
	{
		$delimiter = $this->option('delimiter');
		return trim(preg_replace("|$delimiter{2,}|", $delimiter, $text), $delimiter);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function cutToMaxLength($text)
	{
		if ($this->option('max_length')) {
			$text = substr($text, 0, $this->option('max_length'));
		}
		return $text;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected function applyCase($text)
	{
		switch ($this->option('case')) {
			case 'lower':
				$text = strtolower($text);
				break;
			case 'upper':
				$text = strtoupper($text);
				break;
		}
		return $text;
	}

	/**
	 * @return array
	 */
	protected function defaultOptions()
	{
		return [
			'delimiter' => '-',
			'case' => 'lower'
		];
	}
}