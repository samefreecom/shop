<?php
namespace app\cms\model;

use app\BaseModel;
use think\Db;

class Mold extends BaseModel
{
    public function getMoldById($id)
    {
        $res = $this->where('id', 'eq', $id);
        $mold = $res->find();
        if (empty($mold)) {
            return $this->setErrorCode(100404)->setError('找不到数据！');
        }
        return $mold;
    }
    public function findListByType($type)
    {
        $res = $this->where('type', 'eq', $type);
        return $res->select();
    }
    public function findListByParentId($id)
    {
        $res = $this->where('parent_id', 'eq', $id);
        return $res->select();
    }


    public function getFormatList($list, $type = 'list')
    {
        $relateModel = new Relate();
        $relates = $relateModel->findRelateList(array('type' => $type));
        if (empty($relates)) {
            return $list;
        } else {
            $fmtRelates = array();
            foreach ($relates as $value) {
                $fmtRelates[$value['foreign_id']] = $value['primary_id'];
            }
            $relates = $fmtRelates;
        }
        $returns = array();
        $fmtList = array();
        foreach ($list as $value) {
            if (empty($relates[$value['id']])) {
                $returns[$value['id']] = $value;
            } else {
                $fmtList[$value['id']] = $value;
            }
        }
        foreach ($relates as $childId => $parentId) {
            if (!empty($returns[$parentId]) && !empty($fmtList[$childId])) {
                $returns[$parentId]['childs'][] = $fmtList[$childId];
            }
        }
        return $returns;
    }

    public function findListByMoldId($id)
    {
        $res = $this->where('id', 'eq', $id);
        $mold = $res->find();
        if (!empty($mold)) {
            $objNav = new Nav();
            $list = $objNav->findList(array('mold_id' => $id));
            $list = $this->getFormatList($list);
            return array('mold' => $mold, 'list' => $list);
        }
        return null;
    }

    public function findNavListByDefMold()
    {
        $type = 'nav';
        $res = $this->where('type', 'eq', $type);
        $mold = $res->find();
        if (!empty($mold)) {
            return $this->findListByMoldId($mold['id']);
        }
        return null;
    }
    public function addMoldByName($name)
    {
        if (sfis_valid($_POST, array('name')) === true) {
        } else {
            return $this->setErrorCode(100404)->setError('导航组名称不可为空');
        }
        $bind = array(
            'site_id' => 1
            , 'parent_id' => 0
            , 'type' => 'nav'
            , 'name' => $name
            , 'sort' => 0
            , 'created_at' => date('Y-m-d H:i:s')
            , 'updated_at' => date('Y-m-d H:i:s')
        );
        $result = Db::table(sfp('mold'))->insert($bind);
        if ($result) {
            return true;
        }
        return $this->setErrorNull();
    }

    private function getPathByParentId($id)
    {
        if (empty($id)) {
            return ',0,';
        }
        $path = ',0,';
        $res = $this->where('id', 'eq', $id);
        $mold = $res->find();
        if (!empty($mold)) {
            $path = $mold['path'] . $id . ',';
        }
        return $path;
    }

    public function addMold($param)
    {
        if (($key = sfis_valid($param, array('name', 'type'))) !== true) {
            return $this->setErrorCode(100404)->setError('类目名称不可为空！');
        }
        try {
            $this->begin();
            $bind = array(
                'site_id' => 1
                , 'parent_id' => $param['parent_id']
                , 'path' => $this->getPathByParentId($param['parent_id'])
                , 'type' => $param['type']
                , 'name' => $param['name']
                , 'sort' => 0
                , 'created_at' => date('Y-m-d H:i:s')
                , 'updated_at' => date('Y-m-d H:i:s')
            );
            $result = Db::table(sfp('mold'))->insert($bind);
            if ($result) {
                $id = Db::getLastInsID();
                if (!empty($param['parent_id'])) {
                    $result = Db::table(sfp('mold'))
                        ->where('id', '=', $id)
                        ->update(array('path' => $this->getPathByParentId($param['parent_id']) . $id . ','));
                    if (!$result) {
                        throw new \Exception('更新父关系时失败，请联系技术客服！');
                    }
                }
                $this->commit();
                return true;
            }
        } catch (\Exception $e) {
            $this->rollBack();
            return $this->setException($e);
        }
        return $this->setErrorNull();
    }

    public function deleteMoldById($id)
    {
        $res = $this->where('parent_id', 'eq', $id);
        $subMold = $res->find();
        if (!empty($subMold)) {
            return $this->setErrorCode(100500)->setError('必须先删除子类目，如：%s', $subMold['name']);
        }
        $res = Db::table(sfp('list'))->where('mold_id', 'eq', $id);
        $subList = $res->find();
        if (!empty($subList)) {
            return $this->setErrorCode(100500)->setError('必须先删除子项，如：%s', $subList['title']);
        }
        $result = Db::table(sfp('mold'))
            ->where('id', 'eq', $id)->delete();
        if ($result) {
            return true;
        }
        return $this->setErrorNull();
    }

    private function tree($arr, $id)
    {
        $list = array();
        foreach ($arr as $k => $v){
            if ($v['parent_id'] == $id){
                $v = array('id' => $v['id'], 'label' => $v['name']);
                if (!empty($v['id'])) {
                    $v['children'] = $this->tree($arr, $v['id']);
                }
                $list[] = $v;
            }
        }
        return $list;
    }

    public function getTreeByType($type)
    {
        $list = Db::table(sfp('mold m'))->order('m.sort', 'desc')->where('m.type', "eq", $type)->select();
        $retList = $this->tree($list, 0);
        array_unshift($retList, array('id' => 0, 'label' => '所有类目'));
        return $retList;
    }

    public function modifyNameById($id, $name)
    {
        Db::table(sfp('mold'))
            ->where('id', '=', $id)
            ->update(array('name' => $name, 'updated_at' => date('Y-m-d H:i:s')));
        return true;
    }

    public function modifySort($param)
    {
        foreach ($param as $value) {
            Db::table(sfp('mold'))
                ->where('id', '=', $value['id'])
                ->update(array('sort' => $value['sort'], 'updated_at' => date('Y-m-d H:i:s')));
        }
        return true;
    }

    public function modifyParentById($id, $parentId)
    {
        $newBind = array('parent_id' => (int)$parentId, 'path' => $this->getPathByParentId($parentId) . $id . ',', 'updated_at' => date('Y-m-d H:i:s'));
        Db::table(sfp('mold'))
            ->where('id', '=', $id)
            ->update($newBind);
        return true;
    }

    public function find($param)
    {
        $base = Db::table(sfp('mold'));
        $base->where('name', 'eq', $param['name']);
        $base->limit(1);
        $data = $base->find();
        return $data;
    }
}