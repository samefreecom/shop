<?php
namespace app\cms\model;

use app\BaseModel;
use think\Db;
use think\facade\Env;

class Code extends BaseModel
{
    private $codes = [];
    public function bindWhere(&$base, $param = array())
    {
        if (!empty($param['key'])) {
            $base->where('key like "%' . $param['key'] . '%"');
        }
        if (!empty($param['code'])) {
            $base->where('code like "%' . $param['code'] . '%"');
        }
    }

    public function find($param)
    {
        if (sfget_valid_one($param, array('id')) === false) {
            return $this->setErrorCode(100404)->setError('缺少必要参数！');
        }
        $base = Db::table(sfp('code'));
        $base->where('id', 'eq', $param['id']);
        $base->limit(1);
        $data = $base->find();
        return $data;
    }

    public function getCodeById($id)
    {
        $data = $this->find(array('id' => $id));
        return $data;
    }

    public function findList($param)
    {
        $base = Db::table(sfp('code t'));
        $this->bindWhere($base, $param);
        if (!empty($param['limit'])) {
            if (!empty($param['page'])) {
                $base->limit($param['limit'], $param['limit'] * ($param['page'] - 1));
            } else {
                $base->limit($param['limit']);
            }
        }
        if (!empty($param['order'])) {
            $base->order(implode(" ", $param['order']));
        }
        $base->order('updated_at', 'desc');
        $list = $base->select();
        return $list;
    }

    public function findListTotal($param = array())
    {
        $base = Db::table(sfp('code t'));
        $this->bindWhere($base, $param);
        $total = $base->count('id');
        return $total;
    }

    public function isValid($param)
    {
        if (($key = sfis_valid($param, array('key', 'note', 'content'))) !== true) {
            switch ($key) {
                case 'key':
                    return $this->setErrorCode(100500)->setError('请输入唯一代码，例如：index-about');
                    break;
            }
            return $this->setErrorCode(100500)->setError($key . '参数找不到！');
        }
        return true;
    }

    public function addCode($param)
    {
        if (!$this->isValid($param)) {
            return false;
        }
        try {
            $bind = array(
                'key' => $param['key']
                , 'content' => $param['content']
                , 'note' => $param['note']
                , 'created_at' => date('Y-m-d H:i:s')
                , 'updated_at' => date('Y-m-d H:i:s')
            );
            if (!empty($param['img'])) {
                $bind['preview_path'] = $param['img'];
            }
            $result = Db::table(sfp('code'))->insert($bind);
            if ($result) {
                return true;
            }
        } catch (\Exception $e) {
            return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
        }
        return $this->setErrorCode(100400)->setError('未知错误！');
    }

    public function modifyCode($param)
    {
        if (empty($param['id'])) {
            return $this->setErrorCode(100404)->setError('缺少id参数！');
        }
        if (!$this->isValid($param)) {
            return false;
        }
        $bind = array(
            'key' => $param['key']
            , 'content' => $param['content']
            , 'note' => $param['note']
            , 'updated_at' => date('Y-m-d H:i:s')
        );
        if (!empty($param['img'])) {
            $bind['preview_path'] = $param['img'];
        }
        try {
            Db::table(sfp('code'))->where('id', 'eq', $param['id'])->update($bind);
            return true;
        } catch (\Exception $e) {
            return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
        }
    }

    public function write($key)
    {
        if (isset($this->codes[$key])) {
            echo str_replace('__STATIC__', '/public/static', $this->codes[$key]['content']);
            return true;
        }
        $base = Db::table(sfp('code'));
        $base->field('content');
        $base->where('key', 'eq', $key);
        $base->limit(1);
        $data = $base->find();
        if (empty($data)) {
            $data = array(
                'content' => $data['content']
            );
        }
        $this->codes[$key] = $data;
        echo str_replace('__STATIC__', '/public/static', $this->codes[$key]['content']);
        return true;
    }
}