<?php
namespace TAO\Text\Processor;

use TAO\Text\ProcessorInterface;

/**
 * Class Translit
 *
 * Обработчик для обычного транслита текста. Не поддерживает никаких настроек и непроизводит никаких преобразований
 * кроме замены кириллических букв на транслитерированные значения.
 *
 * @package TAO\Text\Processor
 */
class Translit implements ProcessorInterface
{
    /**
     * @param string $text
     * @return string
     */
    public function process($text)
    {
        return $this->translit($text);
    }

    /**
     * @param string $text
     * @return string
     */
    protected function translit($text)
    {
        return str_replace(array_keys($this->matchTable()), $this->matchTable(), $text);
    }

    /**
     * @return array
     */
    protected function matchTable()
    {
        return ["А" => "A", "а" => "a", "Б" => "B", "б" => "b", "В" => "V", "в" => "v", "Г" => "G", "г" => "g",
            "Д" => "D", "д" => "d", "Е" => "E", "е" => "e", "Ё" => "E", "ё" => "e", "Ж" => "Zh", "ж" => "zh",
            "З" => "Z", "з" => "z", "И" => "I", "и" => "i", "Й" => "I", "й" => "i", "К" => "K", "к" => "k", "Л" => "L",
            "л" => "l", "М" => "M", "м" => "m", "Н" => "N", "н" => "n", "О" => "O", "о" => "o", "П" => "P", "п" => "p",
            "Р" => "R", "р" => "r", "С" => "S", "с" => "s", "Т" => "T", "т" => "t", "У" => "U", "у" => "u", "Ф" => "F",
            "ф" => "f", "Х" => "Kh", "х" => "kh", "Ц" => "Tc", "ц" => "tc", "Ч" => "Ch", "ч" => "ch", "Ш" => "Sh",
            "ш" => "sh", "Щ" => "Shch", "щ" => "shch", "Ы" => "Y", "ы" => "y", "Э" => "E", "э" => "e", "Ю" => "Iu",
            "ю" => "iu", "Я" => "Ia", "я" => "ia", "ъ" => "", "ь" => ""];
    }
}