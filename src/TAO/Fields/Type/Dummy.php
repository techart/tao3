<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Dummy extends Field
{
    public function checkSchema(Blueprint $table)
    {
        return $this;
    }

    public function setFromRequest($request)
    {
    }

    public function setFromRequestAfterSave($request)
    {
    }
    
    public function set($value)
    {
    }
    
    public function rawValue()
    {
    }
}
