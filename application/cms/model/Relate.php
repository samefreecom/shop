<?php
namespace app\cms\model;

use think\Model;

class Relate extends Model
{
    public function findRelateList($param)
    {
        if (sfget_valid_one($param, array('type')) === false) {
            return $this->setErrorCode(100404)->setError('缺少必要参数！');
        }
        $this->where('type', 'eq', $param['type'])->order('sort', 'asc');
        $list = $this->select();
        return $list;
    }
}