<?php

namespace TAO\ORM\Traits;

trait Sortable
{
    public function initExtraSortable()
    {
        $this->extraFields = \TAO::merge($this->extraFields, [
            'weight' => array(
                'type' => 'integer index',
                'in_list' => false,
                'in_form' => false,
            ),
        ]);
    }

    public function isSortable()
    {
        return true;
    }
}