<?php
namespace app\ad\controller;

class Admin
{
    public function index()
    {
        $bind = ['title' => '广告管理'];
        return view('ad/admin/index', $bind);
    }
}