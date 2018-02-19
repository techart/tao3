<?php

namespace TAO\Text;
/**
 * Trait ProcessorOptions
 *
 * Трейт для стандартной работы с опциями обработки текстов в обработчиках.
 * @package TAO\Text
 */
trait ProcessorOptions
{
    protected $options;

    /**
     * @param array $options
     */
    protected function initOptions($options = [])
    {
        $this->options = array_replace_recursive($this->defaultOptions(), $options);
    }

    /**
     * @return array
     */
    protected function defaultOptions()
    {
        return [];
    }

    /**
     * @param $optionName
     * @return mixed
     */
    protected function option($optionName)
    {
        return isset($this->options[$optionName]) ? $this->options[$optionName] : false;
    }
}