<?php

namespace TAO\Text\Processor;

use TAO\Text\ProcessorInterface;

class Typographer implements ProcessorInterface
{
	protected $tagExp = '{(</?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)}s';
	private $quotesCounter = 0;

	public function process($text)
	{
		$parts = preg_split($this->tagExp, $text, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		foreach ($parts as $key => $part) {
			if (!$this->hasTag($part)) {
				$part = $this->processText($part);
				$part = $this->processQuo($part);
				$parts[$key] = $part;
			}
		}
		return implode($parts);
	}

	public function prepareRule($rule)
	{
		return "/$rule/iu";
	}

	protected function processQuo($part)
	{
		$quotesCounter = &$this->quotesCounter;
		$part = preg_replace_callback('/"/', function ($matches) use (&$quotesCounter) {
			$quotesCounter++;
			if ($quotesCounter % 2) {
				return '&laquo;';
			} else {
				return '&raquo;';
			}
		}, $part);
		return $part;
	}

	protected function processText($text)
	{
		return preg_replace($this->prepareRules(array_keys($this->rules())), $this->rules(), $text);
	}

	protected function prepositions()
	{
		return ['в', 'без', 'до', 'из', 'к', 'на', 'по', 'о', 'от', 'перед', 'при', 'через', 'с',
			'у', 'и', 'нет', 'за', 'над', 'для', 'об', 'под', 'про', 'не'];
	}

	protected function personalPronouns()
	{
		return ['я', 'меня', 'мне', 'меня', 'мной', 'мною', 'мы', 'нас', 'нам', 'нами', 'ты', 'тебя', 'тебе',
			'тобой', 'тобою', 'вы', 'вас', 'вам', 'вами', 'он', 'его', 'него', 'ему', 'нему', 'им', 'ним', 'она',
			'её', 'неё', 'ей', 'ней', 'ею', 'нею', 'оно', 'они', 'их', 'них', 'ими', 'ними'];
	}

	protected function monthsExps()
	{
		return ['январ', 'феврал', 'март', 'апрел', 'май', 'мая', 'маю', 'мае', 'июн', 'июл', 'август',
			'сентябр', 'октябр', 'ноябр', 'декабр'];
	}

	protected function rules()
	{
		return [
			'\(c\)' => '&copy;',
			' - ' => ' &mdash; ',
			'(\d)-(\d)' => '$1&ndash;$2',
			'  ' => ' ',
			'""' => '"',
			'«' => '&laquo;',
			'»' => '&raquo;',
			'&laquo;\s*&laquo;' => '&laquo;',
			'([a-zA-Zа-яА-Я])\.([a-zA-Zа-яА-Я])\.([a-zA-Zа-яА-Я])' => '$1. $2. $3',
			'([a-zA-Zа-яА-Я])\.([a-zA-Zа-яА-Я])\.' => '$1. $2.',
			'(№|§)(\S)' => '$1 $2',
			'\s(г|пос|д|ул|пр|пер|гл)\.(\S)' => ' $1. $2',
			'\s(г-н|г-жа)(\S)' => ' $1. $2',
			'(\d)(кг|г|гр|мг|л|мл|км\/ч|кВ)([\s.,;)])' => '$1 $2$3',
			'(\s|^)(' . implode('|', $this->wrappableWords()) . ')(\s+)' => '$1$2&nbsp;',
			'(\s+)(-|–|—|&ndash;|&mdash;)' => '&nbsp;$2',
			'(\d{1,2}([а-я]+)?)(\s)((' . implode('|', $this->monthsExps()) . ')[а-яА-Я]+)' => '$1&nbsp;$4',
			'((' . implode('|', $this->monthsExps()) . ')[а-яА-Я]+)(\s)(\d{4})' => '$1&nbsp;$4',
			'(\d{2,4})(\s)(год[а-я]{1,2})' => '$1&nbsp;$3',
			'([А-Я][а-я]+)(\s)(([А-Я]\.)(\s+)?([А-Я]\.))' => '$1&nbsp;$4$6',
		];
	}

	protected function wrappableWords()
	{
		return array_merge($this->personalPronouns(), $this->prepositions());
	}

	private function hasTag($text)
	{
		return preg_match($this->tagExp, $text);
	}

	private function prepareRules($rules)
	{
		return array_map(array($this, 'prepareRule'), $rules);
	}

}
