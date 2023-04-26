<?php
namespace app\seat\controller;

use app\food\model\Base;

class Index
{
    public function index()
    {
        $bind = ['title' => '点餐'];
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
        return view('seat/index', $bind);
    }
}
