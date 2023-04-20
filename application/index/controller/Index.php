<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return view('index', ['title' => '首页']);
    }

    public function test()
    {
        return view('test', ['title' => '测试']);
    }
}
