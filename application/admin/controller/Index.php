<?php
namespace app\admin\controller;

class Index
{
    public function index()
    {
        define('FALG_ADMIN', true);
        if (!isset($_SESSION['_admin_user'])) {
            sfredirect('/admin/login');
        }
        $bind = ['title' => '首页'];
        return view('admin/index', $bind);
    }
}
