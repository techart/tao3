<?php

namespace TAO\ORM\Traits;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use TAO\Fields\Field;

/**
 * Trait View
 *
 * @method Field field($name, $forceType = false)
 */
trait View
{
    public function findView($name, $default = 'tao::no-view-for')
    {
        $factory = app(ViewFactory::class);
        $code = $this->getDatatype();
        $views = [];
        $views[] = "datatypes.{$code}.{$name}";
        $views[] = "tao::datatypes.{$code}.{$name}";
        $views[] = "datatypes.{$name}";
        $views[] = "tao::datatypes.{$name}";
        foreach ($views as $view) {
            if ($factory->exists($view)) {
                return $view;
            }
        }
        return $default;
    }

//    public function renderListPage1($data = [])
//    {
//        $data['page'] = $page = isset($data['page']) ? $data['page'] : 1;
//        $data['per_page'] = $perPage = isset($data['per_page']) ? $data['per_page'] : 10;
//        $data['mode'] = $mode = isset($data['mode']) ? $data['mode'] : 'list';
//        $data['listing'] = $selector = isset($data['listing']) ? $data['listing'] : 'getAccessibleItems';
//        $data['pager_callback'] = isset($data['pager_callback']) ? $data['pager_callback'] : [$this, 'listUrl'];
//        if (isset($data['base'])) {
//            $this->baseListUrl('/' . $data['base'] . '/');
//        }
//        $view = $this->findView($data['mode']);
//
//        $select = $this->$selector();
//        $count = $select->count();
//        $numPages = ceil($count / $perPage);
//        $rows = [];
//        foreach ($select->limit($perPage)->offset(($page - 1) * $this->perPage)->get() as $row) {
//            $rows[] = $row;
//        }
//
//        $data['count'] = $count;
//        $data['numpages'] = $numPages;
//        $data['rows'] = $rows;
//        $data['model'] = $this;
//        $data['item'] = $this;
//        if (!isset($data['title'])) {
//            $data['title'] = $this->adminMenuTitle();
//        }
//        return view($view, $data);
//    }

    /**
     * Рендер детальной страницы итема.
     * На вход могут приходить данные в следующих вариантах:
     *
     * 1. Сам объект итема
     * 2. Массив, в котором есть элемент item
     * 3. Массив, в котором есть элемент id (в этом случае для чтения итема вызывается метод getItemById)
     * 3. Массив, в котором есть элементы id и finder (имя метода вместо getItemById)
     *
     * Если итема нет (не пришел и не найден), то отдается 404
     * Если итем найден, то он рендерится через метод render.
     * Если на вход пришел массив, то он передается в метод render
     *
     * @param array $data
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function renderItemPage($data = [])
    {
        if (is_object($data)) {
            $data = ['item' => $data];
        }
        if (isset($data['item'])) {
            $item = $data['item'];
        } else {
            $finder = isset($data['finder']) ? $data['finder'] : 'getItemById';
            $id = isset($data['id']) ? $data['id'] : 0;
            $item = $this->$finder($id);
        }
        if ($item instanceof Builder) {
            $item = $item->first();
        }
        if (!$item) {
            return response(view('404'), 404);
        }
        $data['mode'] = isset($data['mode']) ? $data['mode'] : 'full';
        return $item->render($data);
    }

    /**
     * Рендер итема, в контексте которого вызываатся
     *
     * @param array $data
     * @return ViewFactory|\Illuminate\View\View|array|Response|string
     */
    public function render($data = [])
    {
        $data['mode'] = isset($data['mode']) ? $data['mode'] : 'teaser';
        $data['item'] = $this;
        $view = $this->findView($data['mode']);
        foreach(array_keys($this->fields()) as $field) {
            if ($p = $this->field($field)->param('in_context')) {
                $data[is_string($p)? $p : $field] = $this->field($field);
            }
        }
        $r = $this->beforeRender($data, $view);
        if (is_array($r)) {
            $data = \TAO::merge($data, $r);
        } elseif (is_string($r) || $r instanceof Response) {
            return $r;
        }
        return view($view, $data);
    }

    /**
     * Хук перед рендером итема. Можно модифицировать сам итем, данные передаваемые в шаблон, а также респонс
     *
     * Если возвращает массив, то он мержится с даннымиЮ передаваемыми в шаблон
     * Если возвращает респонс, то он от отправится по инстанциям вместо результата рендера (имеет смысл только при рендере целой страницы)
     *
     * @param $data - данные, передаваемые в шаблон
     * @param $view - имя шаблона
     *
     * @return string|Response|array|void
     */
    protected function beforeRender($data, $view)
    {
    }
}