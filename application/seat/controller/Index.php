<?php
namespace app\seat\controller;

class Index
{
    public function index()
    {
        return view('seat/index', ['title' => '点餐']);
    }
}
