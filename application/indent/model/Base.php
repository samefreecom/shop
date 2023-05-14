<?php
namespace app\indent\model;

use app\base\model\Session;
use app\BaseModel;
use think\Db;
class Base extends BaseModel
{
    public function findList($param = array())
    {
        $base = Db::table(sfp('food t'));
        $base->join(sfp('variety v'), 't.variety_id = v.id');
        $base->order('v.sort desc');
        $base->order('t.sort desc');
        $base->order('t.id desc');
        $base->field(['t.*', 'v.id' => 'variety_id', 'v.name' => 'variety_name', 'v.sort' => 'variety_sort']);
        if (isset($param['min_quantity'])) {
            $base->where('quantity', '>=', $param['min_quantity']);
        }
        $list = $base->select();
        return $list;
    }
    
    public function createIdent($param)
    {
        sftrim($param);
        $keyMap = ['group_id' => '团体编号', 'account_id' => '用户编号', 'ids' => '菜品集合', 'qtys' => '数量集合'];
        if (($key = sfis_valid($param, array('group_id', 'account_id', 'ids', 'qtys'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        try {
            $this->startTrans();
            $mapId = array_flip($param['ids']);
            $mapData = [];
            $sumPrice = 0.00;
            $foodList = Db::table(sfp('food'))->where('id', 'in', $param['ids'])->select();
            foreach ($foodList as $value) {
                $i = $mapId[$value['id']];
                $qty = $param['qtys'][$i];
                if (!isset($mapData[$value['id']])) {
                    $mapData[$value['id']] = array_merge($value, array('qty' => $qty));
                    $sumPrice += $value['price'] * $qty;
                } else {
                    $mapData[$value['id']]['qty'] += $qty;
                    $sumPrice += $mapData[$value['id']]['price'] * $qty;
                }
            }
            $exists = Db::table(sfp('indent'))->where('account_id', 'eq', $param['account_id'])->where('status', 'eq', 'W')->where('to_days(created_at) = to_days(now())')->find();
            $bind = [
                'group_id' => $param['group_id']
                , 'account_id' => $param['account_id']
                , 'indent_no' => sfget_unique()
                , 'amount' => $sumPrice
                , 'created_at' => date('Y-m-d H:i:s')
                , 'created_ip' => sfget_ip()
                , 'updated_at' => date('Y-m-d H:i:s')
            ];
            if (isset($param['lon'])) {
                $bind['created_lon'] = $param['lon'];
            }
            if (isset($param['lat'])) {
                $bind['created_lat'] = $param['lat'];
            }
            if (Db::table(sfp('indent'))->insert($bind)) {
                $indentId = Db::table(sfp('indent'))->getLastInsID();
                $foodBindList = [];
                foreach ($mapData as $value) {
                    $foodBindList[] = array(
                        'indent_id' => $indentId
                        , 'food_id' => $value['id']
                        , 'price' => $value['price']
                        , 'quantity' => $value['qty']
                        , 'created_at' => date('Y-m-d H:i:s')
                        , 'updated_at' => date('Y-m-d H:i:s')
                    );
                }
                if (Db::table(sfp('indent_food'))->insertAll($foodBindList)) {
                    if (!empty($exists)) {
                        Db::table(sfp('indent'))->where('id', 'eq', $exists['id'])->update(array('status' => 'E', 'updated_at' => date('Y-m-d H:i:s')));
                    }
                    $this->commit();
                    return true;
                } else {
                    throw new \Exception('插入数据库失败，请联系技术客服！');
                }
            } else {
                throw new \Exception('插入数据库失败，请联系技术客服！');
            }
        } catch (\Exception $e) {
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function getList($param)
    {
        $accountId = Session::instance()->getId();
        $base = Db::table(sfp('indent t'))
            ->leftJoin(sfp('group g'), 't.group_id = g.id')
            ->leftJoin(sfp('status s'), 't.status = s.status_code')
            ->where('t.account_id', 'eq', $accountId)
            ->order('t.id desc')
            ->field(['t.*', 'g.title', 'g.lon', 'g.lat', 's.status_indent']);
        if (!empty($param['status']) && $param['status'] != 'A') {
            $base->where('status', 'eq', $param['status']);
        }
        $list = $base->select();
        $indentIdList = [];
        $foodIdList = [];
        $fmtIndentList = [];
        foreach ($list as $value) {
            $indentIdList[] = $value['id'];
            $value['foodList'] = [];
            $value['food_sum_qty'] = 0;
            $fmtIndentList[$value['id']] = $value;
        }
        $indentFoodList = Db::table(sfp('indent_food'))->where('indent_id', 'in', $indentIdList)->select();
        foreach ($indentFoodList as $value) {
            $foodIdList[] = $value['food_id'];
        }
        $foodList = Db::table(sfp('food'))->where('id', 'in', $foodIdList)->select();
        $fmtFoodList = [];
        foreach ($foodList as $value) {
            $fmtFoodList[$value['id']] = $value;
        }
        foreach ($indentFoodList as $value) {
            if (isset($fmtFoodList[$value['food_id']])) {
                $value['name'] = $fmtFoodList[$value['food_id']]['name'];
                $value['image'] = $fmtFoodList[$value['food_id']]['image'];
            } else {
                $value['name'] = '已作废';
                $value['image'] = '';
            }
            $fmtIndentList[$value['indent_id']]['foodList'][] = $value;
            $fmtIndentList[$value['indent_id']]['food_sum_qty'] += $value['quantity'];
        }
        $list = array_merge($fmtIndentList);
        return $list;
    }
}