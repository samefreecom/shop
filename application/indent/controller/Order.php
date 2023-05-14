<?php
namespace app\indent\controller;
use app\indent\model\Base;
use think\facade\Request;

class Order
{
    public function index()
    {
        $param = Request::param();
        $status = sfret('status', 'A');
        $mIndent = new Base();
        $bind = ['title' => '全部外卖'];
        $bind['list'] = $mIndent->getList($param);
        $bind['status'] = $status;
        return view('indent/order/index', $bind);
    }

    public function show()
    {
        return $this->index();
    }
}
