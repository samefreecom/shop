<?php
namespace app\food\model;

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
    
    public function add($param)
    {
        sftrim($param);
        $exists = Db::table(sfp('food'))->where('name', 'eq', $param['name'])->find();
        if (!empty($exists)) {
            return $this->setError('菜品：' . $param['name'] . ' 已存在！');
        }
        $keyMap = ['image' => '商品图片', 'name' => '商品名称', 'sort' => '排序优先级', 'price' => '商品单价', 'quantity' => '剩余库存'];
        if (($key = sfis_valid($param, array('image', 'name', 'sort' => 'numeric', 'price' => 'numeric', 'quantity' => 'numeric'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $bind = [
            'image' => $param['image']
            , 'name' => $param['name']
            , 'sort' => $param['sort']
            , 'variety_id' => $param['variety_id']
            , 'price' => $param['price']
            , 'quantity' => $param['quantity']
        ];
        if (empty($bind['variety_id'])) {
            $bind['variety_id'] = 0;
        }
        if (Db::table(sfp('food'))->insert($bind)) {
            return true;
        } else {
            return $this->setError('插入数据库失败，请联系技术客服！');
        }
    }
}