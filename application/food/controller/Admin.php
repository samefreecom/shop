<?php
namespace app\food\controller;

use app\food\model\Base;
use app\food\model\Variety;
use think\facade\Request;

class Admin
{
    public function index()
    {
        $bind = ['title' => '菜单管理'];
        $mFood = new Base();
        $bind['list'] = $mFood->findList();
        return view('food/admin/index', $bind);
    }
    
    public function add()
    {
        $param = Request::param();
        if (Request::isPost()) {
            $mFood = new Base();
            if ($mFood->add($param)) {
                sfpush_admin_tmp_message('添加菜单成功');
                if (isset($param['subtype']) && $param['subtype'] == '确认并继续') {
                    sfredirect();
                } else {
                    sfredirect(sfurl('/admin/item/food'));
                }
            } else {
                sfpush_admin_tmp_message($mFood->getError());
            }
        }
        $mVariety = new Variety();
        $bind = ['title' => '菜单管理'];
        $bind['varietyList'] = $mVariety->findList();
        return view('food/admin/add', $bind);
    }
    
    public function addVariety()
    {
        $ret = [
            'code' => 0
            , 'msg' => ''
        ];
        $param = Request::param();
        $mVariety = new Variety();
        if ($mVariety->add($param)) {
            $ret['code'] = 200;
            $ret['data'] = $mVariety->getAttr('data');
        } else {
            $ret['msg'] = $mVariety->getError();
        }
        sfquit(json_encode($ret));
    }
    
    public function delVariety()
    {
        $ret = [
            'code' => 0
            , 'msg' => ''
        ];
        $param = Request::param();
        $mVariety = new Variety();
        if ($mVariety->del($param)) {
            $ret['code'] = 200;
        } else {
            $ret['msg'] = $mVariety->getError();
        }
        sfquit(json_encode($ret));
    }
}