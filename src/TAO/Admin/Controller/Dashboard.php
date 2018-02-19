<?php

namespace TAO\Admin\Controller;

class Dashboard extends Base
{
    public function index()
    {
        return $this->render('admin ~ dashboard');
    }
}