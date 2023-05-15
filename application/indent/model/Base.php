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
            $group = Db::table(sfp('group'))->where('id', 'eq', $param['group_id'])->field('group_no')->find();
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
            $exists = Db::table(sfp('indent'))->where('account_id', 'eq', $param['account_id'])->where('group_id', 'eq', $param['group_id'])->where('status', 'eq', 'W')->where('to_days(created_at) = to_days(now())')->find();
            $max = Db::table(sfp('indent'))->where('created_at', '>=', date('Y-m-d'))->count('1');
            $autoNo = sprintf('%03d', $max + 1);
            $bind = [
                'group_id' => $param['group_id']
                , 'account_id' => $param['account_id']
                , 'indent_no' => $group['group_no'] . $autoNo
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
                //防止tag_no冲突尝试十次
                for ($i = 0; $i < 10; $i++) {
                    try {
                        $max = Db::table(sfp('indent_food'))->where('created_at', '>=', date('Y-m-d'))->count('1');
                        $autoNo = $max + 1;
                        $foodBindList = [];
                        foreach ($mapData as $value) {
                            $foodBindList[] = array(
                                'group_id' => $param['group_id']
                                , 'indent_id' => $indentId
                                , 'food_id' => $value['id']
                                , 'tag_no' => $autoNo++
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
                            sleep(1);
                        }
                    } catch (\Exception $e) {
                        if ($i == 9) {
                            throw new \Exception('插入数据库失败，请联系技术客服！');
                        } else {
                            sleep(1);
                        }
                    }
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
        if (isset($param['status'])) {
            $param['status'] = strtoupper($param['status']);
        }
        $accountId = Session::instance()->getId();
        $base = Db::table(sfp('indent t'))
            ->leftJoin(sfp('group g'), 't.group_id = g.id')
            ->leftJoin(sfp('status s'), 't.status = s.status_code')
            ->where('t.account_id', 'eq', $accountId)
            ->order('t.id desc')
            ->field(['t.*', 'g.title', 'g.lon', 'g.lat', 's.status_indent']);
        if (!empty($param['status']) && $param['status'] != 'A') {
            if ($param['status'] == 'W') {
                $base->where('t.status', 'in', ['W', 'S']);
            } else {
                $base->where('t.status', 'eq', $param['status']);
            }
        } else {
            $base->where('t.status', 'not in', ['E', 'N']);
        }
        $list = $base->select();
        $indentIdList = [];
        $foodIdList = [];
        $fmtIndentList = [];
        foreach ($list as $value) {
            $indentIdList[] = $value['id'];
            $value['foodList'] = [];
            $value['foodSumQty'] = 0;
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
            $fmtIndentList[$value['indent_id']]['foodSumQty'] += $value['quantity'];
        }
        $list = array_merge($fmtIndentList);
        return $list;
    }

    public function getGroupList($param)
    {
        if (isset($param['status'])) {
            $param['status'] = strtoupper($param['status']);
        }
        $base = Db::table(sfp('group t'))
            ->where('to_days(created_at) = to_days(now())')
            ->leftJoin(sfp('status s'), 't.status = s.status_code')
            ->field(['t.*', 's.status_indent'])
            ->order('t.id desc');
        if (!empty($param['status']) && $param['status'] != 'A') {
            if ($param['status'] == 'W') {
                $base->where('status', 'in', ['W', 'S']);
            } else {
                $base->where('status', 'eq', $param['status']);
            }
        } else {
            $base->where('status', 'not in', ['E', 'N']);
        }
        $list = $base->select();
        $groupIdList = [];
        $indentIdList = [];
        $foodIdList = [];
        $fmtGroupList = [];
        foreach ($list as $value) {
            $groupIdList[] = $value['id'];
            $value['indentList'] = [];
            $value['foodSumQty'] = 0;
            $value['sumAmount'] = 0;
            $fmtGroupList[$value['id']] = $value;
        }
        $baseIndent = Db::table(sfp('indent t'))
            ->leftJoin(sfp('account a'), 't.account_id = a.id')
            ->field(['t.*', 'a.name' => 'account_name'])
            ->where('group_id', 'in', $groupIdList);
        $baseIndent->where('status', 'not in', ['E', 'N']);
        $indentList = $baseIndent->select();
        $fmtIndentList = [];
        foreach ($indentList as $value) {
            $indentIdList[] = $value['id'];
            $value['foodList'] = [];
            $value['foodSumQty'] = 0;
            $fmtIndentList[$value['id']] = $value;
        }
        $baseIndentFood = Db::table(sfp('indent_food t'))
            ->where('t.indent_id', 'in', $indentIdList);
        $indentFoodList = $baseIndentFood->select();
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
            $fmtIndentList[$value['indent_id']]['foodSumQty'] += $value['quantity'];
        }
        foreach ($fmtIndentList as $value) {
            if (!empty($value['foodList'])) {
                $fmtGroupList[$value['group_id']]['indentList'][] = $value;
                $fmtGroupList[$value['group_id']]['foodSumQty'] += $value['foodSumQty'];
                $fmtGroupList[$value['group_id']]['sumAmount'] += $value['amount'];
            }
        }
        foreach ($fmtGroupList as $key => $value) {
            if (empty($value['indentList'])) {
                unset($fmtGroupList[$key]);
            }
        }
        $list = array_merge($fmtGroupList);
        return $list;
    }

    public function updateNote($param)
    {
        sftrim($param);
        $keyMap = ['id' => '外卖编号', 'account_id' => '用户编号', 'content' => '备注'];
        if (($key = sfis_valid($param, array('id', 'account_id', 'content'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $bind = ['note' => $param['content'], 'updated_at' => date('Y-m-d H:i:s')];
        if (Db::table(sfp('indent'))->where('id', 'eq', $param['id'])->where('account_id', 'eq', $param['account_id'])->update($bind)) {
            return true;
        }
        return $this->setErrorCode(500)->setError('无权修改，请刷新后再试');
    }

    public function close($param)
    {
        sftrim($param);
        $keyMap = ['id' => '外卖编号', 'account_id' => '用户编号', 'content' => '原因'];
        if (($key = sfis_valid($param, array('id', 'account_id', 'content'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $bind = ['remark' => $param['content'], 'status' => 'N', 'updated_at' => date('Y-m-d H:i:s')];
        if (Db::table(sfp('indent'))->where('id', 'eq', $param['id'])->where('account_id', 'eq', $param['account_id'])->where('status', 'in', ['W'])->update($bind)) {
            return true;
        }
        return $this->setErrorCode(500)->setError('无权取消，请刷新后再试');
    }
}