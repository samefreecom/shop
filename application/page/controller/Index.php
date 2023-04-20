<?php
namespace app\page\controller;

class Index
{
    public function index()
    {
        $bind = [];
        return view('page/index', $bind);
    }
}
