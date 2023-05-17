<?php
namespace app\cms\model;

use app\BaseModel;
use think\Db;

class Nav extends BaseModel
{
    public function find($param)
    {
        if (sfget_valid_one($param, array('id')) === false) {
            return $this->setErrorCode(100404)->setError('缺少必要参数！');
        }
        $base = Db::table(sfp('mold m'))
            ->join(sfp('list l'), 'm.id = l.mold_id')
            ->join(sfp('entity e'), 'e.type = "nav" and l.id = e.type_id')
            ->field(['l.*', 'e.data' => 'url', 'e.field1' => 'target', 'e.field2' => 'img'])
            ->where('m.type', "eq", "nav");
        if (!empty($param['id'])) {
            $base->where('l.id = :id', ['id' => $param['id']]);
        }
        $base->order('l.sort', 'desc');
        $base->limit(1);
        $list = $base->select();
        return $list;
    }

    public function findList($param)
    {
        if (sfget_valid_one($param, array('mold_id', 'mold_name')) === false) {
            return $this->setErrorCode(100404)->setError('缺少必要参数！');
        }
        $base = Db::table(sfp('mold m'))
            ->join(sfp('list l'), 'm.id = l.mold_id')
            ->join(sfp('entity e'), 'e.type = "nav" and l.id = e.type_id')
            ->field(['l.*', 'e.data' => 'url', 'e.field1' => 'target', 'e.field2' => 'img'])
            ->where('m.type', "eq", "nav");
        if (!empty($param['mold_id'])) {
            $base->where('m.id', 'eq', $param['mold_id']);
        }
        if (!empty($param['mold_name'])) {
            $base->where('m.name', 'eq', $param['mold_name']);
        }
        $base->order('l.sort', 'desc');
        $base->order('l.updated_at', 'asc');
        if (!empty($param['limit'])) {
            if (!empty($param['page'])) {
                $base->limit($param['limit'], $param['limit'] * ($param['page'] - 1));
            } else {
                $base->limit($param['limit']);
            }
        }
        $list = $base->select();
        return $list;
    }

    public function getList($param)
    {
        $list = $this->findList($param);
        $moldModel = new Mold();
        $list = $moldModel->getFormatList($list, 'nav');
        return $list;
    }
    
    public function getNav($param)
    {
        $list = $this->find($param);
        if (!empty($list)) {
            return current($list);
        } else {
            return $this->setErrorCode(100404)->setError('找不到数据！');
        }
    }

    public function addNav($param)
    {
        if (($key = sfis_valid($param, array('mold_id', 'name', 'url'))) !== true) {
            return $this->setErrorCode(100500)->setError($key . '参数找不到！');
        }
        try {
            $this->begin();
            $bindList = array(
                'mold_id' => $param['mold_id']
                , 'title' => $param['name']
                , 'created_at' => date('Y-m-d H:i:s')
                , 'updated_at' => date('Y-m-d H:i:s')
            );
            $result = Db::table(sfp('list'))->insert($bindList);
            if ($result) {
                $lastId = Db::getLastInsID();
                $bindEntity = array(
                    'type' => 'nav'
                    , 'type_id' => $lastId
                    , 'data' => $param['url']
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s')
                );
                if (!empty($param['target'])) {
                    $bindEntity['field1'] = $param['target'];
                }
                if (!empty($param['img'])) {
                    $bindEntity['field2'] = $param['img'];
                }
                $result = Db::table(sfp('entity'))->insert($bindEntity);
                if ($result) {
                    $this->commit();
                    $this->setAttr('id', $lastId);
                    return true;
                } else {
                    throw new \Exception('插入导航失败！');
                }
            }
        } catch (\Exception $e) {
            $this->rollBack();
            return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
        }
        return $this->setErrorCode(100400)->setError('未知错误！');
    }

    public function modifyNav($param)
    {
        if (empty($param['id'])) {
            return $this->setErrorCode(100404)->setError('缺少id参数！');
        }
        $id = $param['id'];
        $bindList = array();
        if (!empty($param['mold_id'])) {
            $bindList['mold_id'] = $param['mold_id'];
        }
        if (!empty($param['name'])) {
            $bindList['title'] = $param['name'];
        }
        try {
            $this->begin();
            if (!empty($bindList)) {
                $res = Db::table(sfp('list'))->where('id', 'eq', $id);
                $data = $res->find();
                if (empty($data)) {
                    return $this->setErrorCode(100404)->setError('找不到导航数据！');
                }
                $bindList['updated_at'] = date('Y-m-d H:i:s');
                Db::table(sfp('list'))->where('id', '=', $param['id'])->update($bindList);
            }
            if (sfget_valid_one($param, array('url', 'target')) !== false) {
                $bindEntity = array('updated_at' => date('Y-m-d H:i:s'));
                if (!empty($param['url'])) {
                    $bindEntity['data'] = $param['url'];
                }
                if (!empty($param['target'])) {
                    $bindEntity['field1'] = $param['target'];
                }
                if (isset($param['img'])) {
                    $bindEntity['field2'] = $param['img'];
                }
                $result = Db::table(sfp('entity'))->where('type', '=', 'nav')->where('type_id', '=', $param['id'])->update($bindEntity);
                if (!$result) {
                    throw new \Exception('更新导航属性失败！');
                }
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollBack();
            return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
        }
    }

    public function deleteNavById($id)
    {
        try {
            $this->begin();
            Db::table(sfp('relate'))
                ->whereRaw('type = "nav" and (primary_id = :id1 or foreign_id = :id2)', array('id1' => $id, 'id2' => $id))->delete();
            $result = Db::table(sfp('entity'))
                ->whereRaw('type = "nav" and type_id = :id', array('id' => $id))->delete();
            if ($result) {
                $result = Db::table(sfp('list'))
                    ->where('id', 'eq', $id)->delete();
                if ($result) {
                    $this->commit();
                    return true;
                } else {
                    throw new \Exception('移除导航失败！');
                }
            } else {
                return $this->setErrorCode(100404)->setError('找不到有效的导航属性！');
            }
        } catch (\Exception $e) {
            $this->rollBack();
            return $this->setErrorCode(100503)->setError('删除异常：' . $e->getMessage());
        }
    }

    public function relateNav($param)
    {
        Db::table(sfp('relate'))
            ->whereRaw('type = "nav" and foreign_id = :id', array('id' => $param['id']))->delete();
        if (!empty($param['sorts'])) {
            foreach ($param['sorts'] as $value) {
                if (empty($value['id']) || $value['id'] == $param['id']) {
                    continue;
                }
                Db::table(sfp('list'))->where('id', '=', $value['id'])->update(array('sort' => $value['val'], 'updated_at' => date('Y-m-d H:i:s')));
            }
        }
        if (!empty($param['parent_id'])) {
            $addBind = array(
                'type' => 'nav'
                , 'primary_id' => $param['parent_id']
                , 'foreign_id' => $param['id']
                , 'sort' => $param['sort']
                , 'created_at' => date('Y-m-d H:i:s')
                , 'updated_at' => date('Y-m-d H:i:s')
            );
            return Db::table(sfp('relate'))
                ->insert($addBind);
        } else {
            return Db::table(sfp('list'))
                ->where('id', '=', $param['id'])
                ->update(array('sort' => $param['sort'], 'updated_at' => date('Y-m-d H:i:s')));
        }
    }
}