<?php
namespace app\seat\controller;

class Admin
{
    public function order()
    {
        $bind = ['title' => '全部订单'];
        return view('seat/admin/order', $bind);
    }
}