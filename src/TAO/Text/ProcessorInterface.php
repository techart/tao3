<?php

namespace TAO\Text;

/**
 * Interface ProcessorInterface
 * @package TAO\Text
 */
interface ProcessorInterface
{
    /**
     * @param string $text
     * @return string
     */
    public function process($text);
}