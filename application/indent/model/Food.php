<?php
namespace app\indent\model;

use app\base\model\Session;
use app\BaseModel;
use think\Db;
class Food extends BaseModel
{
    public function getAccountListByGroupId($groupId)
    {
        $accountId = Session::instance()->getId();
        $base = Db::table(sfp('indent_food t'))
            ->leftJoin(sfp('indent i'), 't.indent_id = i.id')
            ->where('i.group_id', 'eq', $groupId)
            ->where('account_id', 'eq', $accountId)
            ->where('to_days(i.created_at) = to_days(now())')
            ->where('i.status', 'neq', 'E')
            ->field(['t.*']);
        $list = $base->select();
        return $list;
    }

    public function getListByGroupId($groupId)
    {
        $base = Db::table(sfp('indent_food t'))
            ->leftJoin(sfp('indent i'), 't.indent_id = i.id')
            ->leftJoin(sfp('account a'), 'i.account_id = a.id')
            ->leftJoin(sfp('food f'), 't.food_id = f.id')
            ->where('i.group_id', 'eq', $groupId)
            ->where('to_days(i.created_at) = to_days(now())')
            ->where('i.status', 'neq', 'E')
            ->field(['t.*', 'i.account_id', 'a.name' => 'account_name', 'f.name' => 'food_name', 'f.quantity_sale', 'f.image']);
        $list = $base->select();
        $fmtList = [];
        foreach ($list as $value) {
            if (!isset($fmtList[$value['account_id']])) {
                $fmtList[$value['account_id']] = ['id' => $value['account_id'], 'name' => $value['account_name'], 'foodList' => []];
            }
            $fmtList[$value['account_id']]['foodList'][] = $value;
        }
        return $fmtList;
    }
}