<?php
namespace app\admin\controller;

class Index
{
    public function index()
    {
        if (!isset($_SESSION['_admin_user'])) {
            sfredirect('/admin/login');
        }
        $bind = ['title' => '首页'];
        return view('admin/index', $bind);
    }

    public function __call($name, $arguments)
    {
        die('aaa');
    }
}
