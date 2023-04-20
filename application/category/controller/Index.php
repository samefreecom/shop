<?php
namespace app\category\controller;

class Index
{
    public function index()
    {
        return view('category/index', ['title' => '点餐']);
    }
}
