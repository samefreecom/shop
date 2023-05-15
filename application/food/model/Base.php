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
        $base->join(sfp('status s'), 't.status = s.status_code');
        $base->order('sort_quantity desc');
        $base->order('v.sort desc');
        $base->order('t.id desc');
        $base->field(['t.*', 'v.id' => 'variety_id', 'v.name' => 'variety_name', 'v.sort' => 'variety_sort', 's.status_indent', 't.sort + t.quantity' => 'sort_quantity']);
        if (isset($param['status'])) {
            $base->where('status', 'eq', $param['status']);
        }
        if (isset($param['min_quantity'])) {
            $base->where('quantity', '>=', $param['min_quantity']);
        }
        $list = $base->select();
        return $list;
    }

    public function find($param)
    {
        $base = Db::table(sfp('food t'));
        $base->where('id', 'eq', $param['id']);
        $base->limit(1);
        return $base->find();
    }
    
    public function add($param)
    {
        sftrim($param);
        $keyMap = ['image' => '商品图片', 'name' => '商品名称', 'sort' => '排序优先级', 'price' => '商品单价', 'quantity' => '剩余库存'];
        if (($key = sfis_valid($param, array('image', 'name', 'sort' => 'numeric', 'price' => 'numeric', 'quantity' => 'numeric'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $exists = Db::table(sfp('food'))->where('name', 'eq', $param['name'])->find();
        if (!empty($exists)) {
            return $this->setError('菜品：' . $param['name'] . ' 已存在！');
        }
        $bind = [
            'image' => $param['image']
            , 'name' => $param['name']
            , 'sort' => $param['sort']
            , 'variety_id' => $param['variety_id']
            , 'price' => $param['price']
            , 'quantity' => $param['quantity']
            , 'created_at' => date('Y-m-d H:i:s')
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
    
    public function modify($param)
    {
        sftrim($param);
        $keyMap = ['id' => '商品编号', 'image' => '商品图片', 'name' => '商品名称', 'sort' => '排序优先级', 'price' => '商品单价', 'quantity' => '剩余库存'];
        if (($key = sfis_valid($param, array('image', 'name', 'sort' => 'numeric', 'price' => 'numeric', 'quantity' => 'numeric'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $exists = Db::table(sfp('food'))->where('name', 'eq', $param['name'])->where('id', 'neq', $param['id'])->find();
        if (!empty($exists)) {
            return $this->setError('菜品：' . $param['name'] . ' 已存在！');
        }
        $bind = [
            'image' => $param['image']
            , 'name' => $param['name']
            , 'sort' => $param['sort']
            , 'variety_id' => $param['variety_id']
            , 'price' => $param['price']
            , 'quantity' => $param['quantity']
            , 'updated_at' => date('Y-m-d H:i:s')
        ];
        if (empty($bind['variety_id'])) {
            $bind['variety_id'] = 0;
        }
        if (Db::table(sfp('food'))->where('id', 'eq', $param['id'])->update($bind)) {
            return true;
        } else {
            return $this->setError('插入数据库失败，请联系技术客服！');
        }
    }
    
    public function saveQuantity($param)
    {
        $keyMap = ['id' => '菜品编号', 'quantity' => '菜品库存'];
        if (($key = sfis_valid($param, array('id', 'quantity' => 'numeric'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $bind = [
            'quantity' => $param['quantity']
            , 'updated_at' => date('Y-m-d H:i:s')
        ];
        if (Db::table(sfp('food'))->where('id', 'eq', $param['id'])->update($bind)) {
            return true;
        } else {
            return $this->setError('更新库存失败，请联系技术客服！');
        }
    }

    public function doDown($param)
    {
        $keyMap = ['id' => '菜品编号'];
        if (($key = sfis_valid($param, array('id'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $bind = [
            'status' => 'D'
            , 'updated_at' => date('Y-m-d H:i:s')
        ];
        if (Db::table(sfp('food'))->where('id', 'eq', $param['id'])->update($bind)) {
            return true;
        } else {
            return $this->setError('下架失败，请联系技术客服！');
        }
    }

    public function doUp($param)
    {
        $keyMap = ['id' => '菜品编号'];
        if (($key = sfis_valid($param, array('id'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $bind = [
            'status' => 'A'
            , 'updated_at' => date('Y-m-d H:i:s')
        ];
        if (Db::table(sfp('food'))->where('id', 'eq', $param['id'])->update($bind)) {
            return true;
        } else {
            return $this->setError('上架失败，请联系技术客服！');
        }
    }
}