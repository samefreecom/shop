<?php
namespace app\indent\controller;

use app\indent\model\Base;
use think\facade\Request;

class Admin
{
    public function order()
    {
        $param = Request::param();
        $bind = ['title' => '全部订单'];
        $status = sfret('status', 'A');
        $bind['status'] = $status;
        $mIndent = new Base();
        $bind['list'] = $mIndent->getGroupList($param);
        return view('indent/admin/order', $bind);
    }
    
    public function dolock()
    {
        $param = Request::param();
        $mGroup = new \app\group\model\Base();
        if ($mGroup->doLock($param)) {
            sfresponse(1);
        } else {
            sfresponse(0, $mGroup->getError());
        }
    }
    
    public function dodlivery()
    {
        $param = Request::param();
        $mGroup = new \app\group\model\Base();
        if ($mGroup->doDlivery($param)) {
            sfresponse(1);
        } else {
            sfresponse(0, $mGroup->getError());
        }
    }
}