<?php

namespace TAO;

use TAO\Text\ConfigurableProcessorInterface;
use TAO\Text\ProcessorFactory;
use TAO\Text\ProcessorInterface;

class Text
{
    /**
     * Обрабатывает текст указанными обработчиками
     *
     * @param $text
     * @param array|string $processors
     *
     * @return string
     */
    public static function process($text, $processors)
    {
        if (!is_array($processors)) {
            $processors = [$processors];
        }

        foreach ($processors as $key => $value) {
            if (is_array($value)) {
                $processor = $key;
                $processorOptions = $value;
            } else {
                $processor = $value;
                $processorOptions = [];
            }
            if (!($processor instanceof ProcessorInterface) && !($processor instanceof ConfigurableProcessorInterface)) {
                $processor = ProcessorFactory::processor($processor);
            }
            if ($processor instanceof ConfigurableProcessorInterface) {
                $text = $processor->process($text, $processorOptions);
            } else if ($processor instanceof ProcessorInterface) {
                $text = $processor->process($text);
            }
        }
        return $text;
    }
}