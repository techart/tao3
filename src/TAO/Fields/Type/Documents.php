<?php

namespace TAO\Fields\Type;

class Documents extends Attaches
{
    public function inputTemplateFrom()
    {
        return 'attaches';
    }

    public function templateEntryJS()
    {
        return 'js-entry-documents';
    }

    public function infoFieldsSrc()
    {
        return \TAO::merge([
            'text title' => 'Название',
            'date date' => 'Дата',
        ], $this->param('info', []));
    }

    public function isSortable()
    {
        return $this->param('sortable', true);
    }
}
