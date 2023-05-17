<?php
namespace app\ad\controller;

use app\base\model\Setting;
use app\cms\model\Ad;
use app\cms\model\Mold;
use think\facade\Request;

class Admin
{
    public function index()
    {
        $param = Request::param();
        $mModel = new Mold();
        $obj = new Setting();
        $param['type'] = sfret('group', 'ad');
        $bind = ['title' => '广告管理'];
        $bind['type'] = $param['type'];
        $mAd = new Ad();
        $bind['list'] = $mAd->findList($param);
        $bind['moldList'] = $mModel->findListByType($param['type']);
        $bind['groupList'] = $obj->findGroupList();
        return view('ad/admin/index', $bind);
    }
}