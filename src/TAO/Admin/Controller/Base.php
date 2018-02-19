<?php

namespace TAO\Admin\Controller;

/**
 * Class Base
 * @package TAO\Admin\Controller
 */
class Base extends \TAO\Controller
{
    /**
     * @return string
     */
    protected function realm()
    {
        return 'admin';
    }

    /**
     * @return mixed
     */
    public function layout()
    {
        return app()->taoAdmin->layout();
    }

    /**
     * @return string
     */
    protected function urlLogin()
    {
        return '/admin/login';
    }
    
    /**
     * @return $this
     */
    public function setup()
    {
        parent::setup();
        \TAO::setInAdmin(true);
        return $this;
    }

    /**
     * @param $method
     * @param $parameters
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    protected function accessAction($method, $parameters)
    {
        if (!\Auth::check()) {
            return false;
        }
        $rc = \Auth::user()->accessToRealm($this->realm());
        if (!$rc) {
            \Auth::user()->isBlocked = true;
            return response($this->render('404'), 404);
        }
        return true;
    }


}