<?php
namespace app\seat\controller;

use app\base\model\Session;
use app\food\model\Base;
use app\indent\model\Food;
use think\facade\Request;

class Group
{
    public function food($groupId = 0)
    {
        $param = Request::param();
        $param['id'] = sfret('id', $groupId);
        $bind = ['title' => '点餐', 'groupId' => $param['id']];
        $mFood = new Base();
        $bind['list'] = $mFood->findList(array('status' => 'A'));
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
        $mFood = new Food();
        $bind['indentFoodList'] = $mFood->getAccountListByGroupId($param['id']);
        $bind['groupFoodList'] = $mFood->getListByGroupId($param['id']);
        return view('seat/group/index', $bind);
    }
    
    public function quickFood()
    {
        $param = Request::param();
        $mGroup = new \app\group\model\Base();
        $mFood = new Base();
        $group = $mGroup->getQuick();
        if (empty($group)) {
            sfresponse(0, '不支持快捷下单！');
        }
        return $this->food($group['id']);
    }
    
    public function saveIndent()
    {
        $param = Request::param();
        $mSession = new Session();
        $param['account_id'] = $mSession->getId();
        $param['lon'] = $mSession->getLon();
        $param['lat'] = $mSession->getLat();
        $mIndent = new \app\indent\model\Base();
        if ($mIndent->createIdent($param)) {
            sfresponse(1);
        } else {
            sfresponse(0, $mIndent->getError());
        }
    }
}
