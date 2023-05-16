<?php
namespace app\base\controller;

use app\base\model\Setting;
use app\food\model\Base;
use app\food\model\Variety;
use think\facade\Request;

class Admin
{
    public function setting()
    {
        $param = Request::param();

        $obj = new Setting();
        if (Request::isPost()) {
            if ($obj->saveSetting($_POST)) {
                sfresponse(1, '更新成功！');
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $param['group'] = sfret('group', 'default');
        $param['site'] = sfret('site', '0');
        $bind = ['title' => '店铺管理'];
        $bind['groupList'] = $obj->findGroupList();
        $bind['name'] = $obj->getGroupNameByCode($param['group']);
        $bind['list'] = $obj->getList($param);
        $bind['group'] = $param['group'];
        return view('base/admin/setting', $bind);
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
}