<?php

namespace TAO\ORM\Traits;

trait Title
{
    public function initExtraTitle()
    {
        $this->extraFields = \TAO::merge($this->extraFields, [
            'title' => array(
                'type' => 'string(250)',
                'label' => 'Заголовок',
                'style' => 'width:90%;',
                'weight' => -700,
                'in_list' => true,
                'in_form' => true,
                'group' => 'common',
            ),
        ]);
    }
    
    public function title()
    {
        return $this->title;
    }
}