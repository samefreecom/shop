<?php
namespace app\group\controller;

class Index
{
    public function index()
    {
        return view('group/index', ['title' => '团体点餐']);
    }
}
