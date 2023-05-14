<?php
namespace app\group\controller;

use app\group\model\Base;
use think\facade\Request;

class Create
{
    public function index()
    {
        $param = Request::param();
        if (Request::isPost()) {
            $gModel = new Base();
            if ($gModel->add($param)) {
                sfpush_tmp_message('新建团体成功！');
                sfredirect('/group');
            } else {
                sfpush_tmp_message($gModel->getError());
            }
        }
        $bind = ['title' => '团体点餐'];
        return view('group/create', $bind);
    }
}
