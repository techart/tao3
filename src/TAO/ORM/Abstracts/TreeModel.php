<?php

namespace TAO\ORM\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use TAO\Type\Collection;

class TreeModel extends \TAO\ORM\Model
{
    use \TAO\ORM\Traits\Tree,
        \TAO\ORM\Traits\Title;

    protected $itemList = [];

    protected function initExtraFields()
    {
        $this->initExtra('Tree', 'Title');
        $this->extraFields['title']['in_list'] = false;
    }

    public function fields()
    {
        return [];
    }

    public function navigationItem()
    {
        return [
            'url' => $this->url(),
            'title' => $this->title(),
        ];
    }

    public function navigation()
    {
        $args = func_get_args();
        $this->itemList = $this->getAccessibleItems()->get();
        return $this->navigationStruct(0, $args);
    }

    protected function navigationStruct($pid, $options = [])
    {
        $struct = [];
        foreach ($this->itemList as $item) {
            if ($item->parent_id == $pid) {
                $nav = $item->navigationItem($options);
                if (is_array($nav)) {
                    $sub = $this->navigationStruct($item->getKey(), $options);
                    if (is_array($sub) && !empty($sub)) {
                        $nav['sub'] = $sub;
                    }
                    $struct[] = $nav;
                }
            }
        }
        return $struct;
    }

    /**
     * @return Builder
     */
    public function ordered()
    {
        return $this->orderBy('title');
    }

    public function adminMenuSection()
    {
        return 'Словари';
    }
}