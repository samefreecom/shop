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
        $fmtVariety = [];
        $varietyList = [];
        foreach ($bind['list'] as $value) {
            if (empty($value['variety_id'])) {
                continue;
            }
            if (!isset($fmtVariety[$value['variety_name']])) {
                $value['variety_index'] = count($varietyList);
                $varietyList[] = ['name' => $value['variety_name'], 'id' => $value['variety_id'], 'sort' => $value['variety_sort'], 'list' => []];
                $fmtVariety[$value['variety_name']] = $value;
            }
            $varietyList[$fmtVariety[$value['variety_name']]['variety_index']]['list'][] = $value;
        }
        $bind['varietyList'] = $varietyList;
        return view('food/admin/index', $bind);
    }
    
    public function add()
    {
        $param = Request::param();
        if (Request::isPost()) {
            $mFood = new Base();
            if ($mFood->add($param)) {
                sfpush_admin_tmp_message('添加菜品成功');
                if (isset($param['subtype']) && $param['subtype'] == '确认并继续') {
                    sfredirect();
                } else {
                    sfredirect(sfurl('/admin/food'));
                }
            } else {
                sfpush_admin_tmp_message($mFood->getError());
            }
        }
        $bind = ['title' => '菜单管理'];
        $mVariety = new Variety();
        $bind['varietyList'] = $mVariety->findList();
        return view('food/admin/add', $bind);
    }
    
    public function edit()
    {
        $param = Request::param();
        $mFood = new Base();
        if (Request::isPost()) {
            if ($mFood->modify($param)) {
                sfpush_admin_tmp_message('修改菜品成功');
                if (isset($param['subtype']) && $param['subtype'] == '确认并继续') {
                    sfredirect();
                } else {
                    sfredirect(sfurl('/admin/food'));
                }
            } else {
                sfpush_admin_tmp_message($mFood->getError());
            }
        }
        $bind = ['title' => '菜单管理'];
        $mVariety = new Variety();
        $bind['varietyList'] = $mVariety->findList();
        $bind['data'] = $mFood->find(array('id' => $param['id']));
        return view('food/admin/edit', $bind);
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
            $ret['code'] = 1;
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
            $ret['code'] = 1;
        } else {
            $ret['msg'] = $mVariety->getError();
        }
        sfquit(json_encode($ret));
    }
    
    public function saveQuantity()
    {
        $ret = [
            'code' => 0
            , 'msg' => ''
        ];
        $param = Request::param();
        $mFood = new Base();
        if ($mFood->saveQuantity($param)) {
            $ret['code'] = 1;
        } else {
            $ret['msg'] = $mFood->getError();
        }
        sfquit(json_encode($ret));
    }

    public function doDown()
    {
        $ret = [
            'code' => 0
            , 'msg' => ''
        ];
        $param = Request::param();
        $mFood = new Base();
        if ($mFood->doDown($param)) {
            $ret['code'] = 1;
        } else {
            $ret['msg'] = $mFood->getError();
        }
        sfquit(json_encode($ret));
    }
    
    public function doUp()
    {
        $ret = [
            'code' => 0
            , 'msg' => ''
        ];
        $param = Request::param();
        $mFood = new Base();
        if ($mFood->doUp($param)) {
            $ret['code'] = 1;
        } else {
            $ret['msg'] = $mFood->getError();
        }
        sfquit(json_encode($ret));
    }
}