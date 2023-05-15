<?php
namespace app\indent\controller;
use app\base\model\Session;
use app\indent\model\Base;
use think\facade\Request;

class Order
{
    public function index()
    {
        $param = Request::param();
        $status = sfret('status', 'A');
        $bind = ['title' => '全部外卖'];
        $mIndent = new Base();
        $bind['list'] = $mIndent->getList($param);
        $bind['status'] = $status;
        return view('indent/order/index', $bind);
    }

    public function show()
    {
        return $this->index();
    }
    
    public function updateNote()
    {
        $param = Request::param();
        $param['account_id'] = Session::instance()->getId();
        $mIndent = new Base();
        if ($mIndent->updateNote($param)) {
            sfresponse(1);
        } else {
            sfresponse(0, $mIndent->getError());
        }
    }
    
    public function close()
    {
        $param = Request::param();
        $param['account_id'] = Session::instance()->getId();
        $mIndent = new Base();
        if ($mIndent->close($param)) {
            sfresponse(1);
        } else {
            sfresponse(0, $mIndent->getError());
        }
    }
}
