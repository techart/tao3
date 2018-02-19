<?php

namespace TAO\ORM\Traits;

use Illuminate\Database\Eloquent\Builder;
use TAO\ORM\Model;
use TAO\Type\Collection;

trait Tree
{
    public $children = [];

    public $isFirstBranch = false;

    public $isLastBranch = false;

    public $prevBranch = false;

    public $nextBranch = false;


    public function initExtraTree()
    {
        $this->extraFields = \TAO::merge($this->extraFields, [
            'parent_id' => array(
                'type' => 'select index',
                'items' => function() {
                    return $this->buildTreeForParentSelect();
                },
                'label' => 'Родитель',
                'weight' => 100,
                'in_list' => false,
                'in_form' => true,
                'group' => 'common',
            ),
        ]);
    }

    public function isTree()
    {
        return true;
    }
    
    public function buildTreeForParentSelect()
    {
        $args = [0 => 'Корень'];
        if (isset($_GET['filter']['root'])) {
            $rootId = (int)$_GET['filter']['root'];
            /** @var Model|Title $rootItem */
            $rootItem = $this->find($rootId);
            if ($rootItem) {
                $args = [
                    'root' => $rootId,
                    $rootId => $rootItem->title(),
                ];
            }
        }
        return $this->treeForSelect($args);
    }
    
    public function adminTitleList()
    {
        $title = $this->adminTitle();
        if (isset($_GET['filter']['root'])) {
            $rootId = (int)$_GET['filter']['root'];
            /** @var Model|Title $rootItem */
            $rootItem = $this->find($rootId);
            if ($rootItem) {
                $title .= ': '. $rootItem->title();
            }
        }
        return $title;
    }

    /**
     * @param array $args
     * @return array
     */
    public function treeForSelect($args = [])
    {
        $args = Collection::parseString($args);
        $tree = $this->buildTree($args);

        $out = Collection::numericKeysOnly($args);
        $this->buildTreeForSelect($tree, '-&nbsp;&nbsp;&nbsp;', $out);
        return $out;
    }

    /**
     * @param Model[]|Tree $tree
     * @param string $prefix
     * @param array $out
     */
    protected function buildTreeForSelect(&$tree, $prefix, &$out)
    {
        foreach ($tree as $key => $row) {
            $out[$key] = $prefix . $row->title();
            $this->buildTreeForSelect($row->children, $prefix . '-&nbsp;&nbsp;&nbsp;', $out);
        }
    }

    /**
     * @param array $filter
     * @return Model[]
     */
    public function buildTree($filter = [])
    {
        $root = isset($filter['root']) ? $filter['root'] : 0;
        $maxDepth = isset($filter['max_depth']) ? $filter['max_depth'] : 10000;
        return $this->buildTreeFromRows($this->ordered()->get(), $root, $maxDepth);
    }

    /**
     * @param Builder $query
     * @param int $root
     * @param int $maxDepth
     * @return array
     */
    public function buildTreeFromQuery($query, $root = 0, $maxDepth = 10000)
    {
        return $this->buildTreeFromRows($query->get(), $root, $maxDepth);
    }

    /**
     * @param \Illuminate\Support\Collection|Model[] $rows
     * @param int $root
     * @param int $maxDepth
     * @return array
     */
    public function buildTreeFromRows($rows, $root = 0, $maxDepth = 10000)
    {
        return $this->buildTreeBranch($rows, $root, $maxDepth);
    }


    /**
     * @param \Illuminate\Support\Collection|Model[] $rows $rows
     * @param int $root
     * @param int $maxDepth
     * @return Model[]
     */
    public function buildTreeBranch($rows, $root, $maxDepth = 10000)
    {
        $out = [];
        $first = true;
        $prev = false;
        foreach ($rows as $row) {
            if ($row[$this->parentKeyField] == $root) {
                if ($first) {
                    $row->isFirstBranch = true;
                    $first = false;
                }
                if ($prev) {
                    $row->prevBranch = $prev;
                    $prev->nextBranch = $row;
                    $prev->isLastBranch = false;
                }

                if ($maxDepth>0) {
                    $row->children = $this->buildTreeBranch($rows, $row->getKey(),$maxDepth-1);
                }
                $out[$row->getKey()] = $row;
                $prev = $row;
                $row->isLastBranch = true;
            }
        }
        return $out;
    }

    public function adminMaxDepth()
    {
        return 10000;
    }
}