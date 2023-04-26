<?php
namespace app\seat\controller;

use app\food\model\Base;

class Order
{
    public function index()
    {
        $bind = ['title' => '全部外卖'];
        $mFood = new Base();
        $bind['list'] = $mFood->findList();
        return view('seat/order', $bind);
    }
}
