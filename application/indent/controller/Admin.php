<?php
namespace app\indent\controller;

use think\facade\Request;

class Admin
{
    public function order()
    {
        $param = Request::param();
        $bind = ['title' => '全部订单'];
        $status = sfret('status', 'A');
        $bind['status'] = $status;
        return view('indent/admin/order', $bind);
    }
}