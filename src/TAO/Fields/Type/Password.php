<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

/**
 * Class Password
 * @package TAO\Fields\Type
 */
class Password extends StringField
{
    /**
     * @param Blueprint $table
     * @return \Illuminate\Support\Fluent
     */
    public function createField(Blueprint $table)
    {
        return $table->string($this->name, 150);
    }

    /**
     * @return null|string
     */
    public function styleForAdminInput()
    {
        $style = parent::styleForAdminInput();
        return empty($style) ? 'width:80%' : $style;
    }

    /**
     * @param $controller
     * @return array
     */
    public function apiActionGenerate($controller)
    {
        $src = \Request::get('src');
        $hash = empty($src) ? '' : \Hash::make($src);
        return ['response' => $hash];
    }
}
