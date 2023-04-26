<?php
namespace app\food\model;

use app\BaseModel;
use think\Db;
class Variety  extends BaseModel
{
    public function findList($param = array())
    {
        $base = Db::table(sfp('variety'));
        $list = $base->select();
        return $list;
    }

    public function add($param)
    {
        sftrim($param);
        $this->setAttr('data', []);
        $exists = Db::table(sfp('variety'))->where('name', 'eq', $param['name'])->find();
        if (!empty($exists)) {
            return $this->setError('类目：' . $param['name'] . ' 已存在！');
        }
        $bind = [
            'name' => $param['name']
        ];
        $bind['created_at'] = date('Y-m-d H:i:s');
        if (Db::table(sfp('variety'))->insert($bind)) {
            $bind['id'] = Db::table(sfp('variety'))->getLastInsID();
            $this->setAttr('data', $bind);
        }
        return true;
    }

    public function del($param)
    {
        $foodList = Db::table(sfp('food'))->where('variety_id', $param['id'])->select();
        if (!empty($foodList)) {
            $exists = '';
            foreach ($foodList as $value) {
                $exists .= '，' . $value['name'];
            }
            $exists = mb_substr($exists, 1, mb_strlen($exists, 'UTF-8') - 1, 'UTF-8');
            return $this->setError("需移除以下菜品的类目才能删除：\n" . $exists);
        }
        if (Db::table(sfp('variety'))->where('id', 'eq', $param['id'])->delete()) {
            return true;
        }
        return false;
    }
}