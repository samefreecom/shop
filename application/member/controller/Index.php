<?php
namespace app\member\controller;

class Index
{
    public function index()
    {
        return view('member/index', ['title' => '个人中心']);
    }
}
