<?php

$dt = app('tao.admin')->menu();



$site = isset($dt['*site'])? $dt['*site'] : [];
$site = isset($site['sub'])? $site['sub'] : [];
unset($dt['*site']);

$defaultSite =  array(
    array(
        'url' => '/admin/vars/',
        'title' => 'Настройки',
        'icon' => 'cog',
    ),
);

$users =  array(
    array(
        'url' => '/admin/datatype/users/',
        'title' => 'Пользователи',
        'icon' => 'users',
        'labels' => dt('users')->count(),
        'divider' => true,
    ),
    array(
        'url' => '/admin/datatype/roles/',
        'icon' => 'tasks',
        'title' => 'Роли',
    ),
);


$site = array_merge($defaultSite, $site);
$site = array_merge($site, $users);


$nav = array(
    '*main' => array(
        'url' => '/admin/',
        'title' => 'Главная',
    ),
    '*site' => array(
        'access' => 'root',
        'url' => '/admin/vars/',
        'title' => 'Сайт',
        'sub' => $site,
    ),
);

$nav = array_merge($nav, $dt);
return $nav;

