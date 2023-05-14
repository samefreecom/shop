<?php
namespace app\group\controller;

use app\group\model\Base;
use think\facade\Request;

class Search
{
    public function index()
    {
        $param = Request::param();
        $param['limit'] = 3;
        $param['page'] = sfret('page', 1);
        $mGroup = new Base();
        $bind['list'] = $mGroup->findList($param);
        sfresponse(1, '', view('group/item', $bind)->getContent());
    }
}
