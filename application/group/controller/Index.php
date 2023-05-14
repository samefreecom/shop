<?php
namespace app\group\controller;

class Index
{
    public function index()
    {
        $bind = ['title' => '团体点餐'];
        return view('group/index', $bind);
    }

    public function create()
    {
        $bind = ['title' => '团体点餐'];
        return view('group/index', $bind);

    }
}
