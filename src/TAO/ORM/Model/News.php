<?php

namespace TAO\ORM\Model;
use TAO\ORM\Abstracts\PageModel;

class News extends PageModel
{
    protected $table = 'news';

    public function fields()
    {
        return array(
            'idate' => array(
                'type' => 'date_integer index',
                'label' => 'Дата',
                'in_list' => true,
                'in_form' => true,
                'group' => 'common',
            ),
            'announce' => array(
                'type' => 'text',
                'label' => 'Анонс',
                'style' => 'width:90%;height:100px;',
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
            'content' => array(
                'type' => 'text',
                'label' => 'Текст страницы',
                'style' => 'width:90%;height:400px;',
                'in_list' => false,
                'in_form' => true,
                'group' => 'content',
            ),
        );
    }

    public function adminMenuSection()
    {
        return 'Материалы';
    }

    public function typeTitle()
    {
        return 'Новости';
    }

    public function adminTitleEdit()
    {
        return 'Редактирование новости';
    }

    public function adminTitleAdd()
    {
        return 'Создание новости';
    }

    public function adminAddButtonText()
    {
        return 'Создать новость';
    }
}
