<?php

namespace TAO\Text\Processor\Parser;

use TAO\Text\ProcessorInterface;

/**
 * Class Markdown
 *
 * Парсер для проебразования markdown-разметки в html-код.
 *
 * @package TAO\Text\Processor\Parser
 */
class Markdown implements ProcessorInterface
{
    protected $parser;

    /**
     * @param string $text
     * @return string
     */
    public function process($text)
    {
        return $this->parser()->parse($text);
    }

    /**
     * @return \Parsedown
     */
    protected function parser()
    {
        if (!$this->parser) {
            $this->parser = new \Parsedown();
        }
        return $this->parser;
    }
}