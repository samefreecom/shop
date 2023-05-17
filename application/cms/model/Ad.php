<?php
    namespace app\cms\model;

    use app\BaseModel;
    use think\Db;
    class Ad extends BaseModel
    {
        public function bindWhere(&$base, $param = array())
        {
            $base->where('m.type', 'eq', 'ad');
            if (!empty($param['id'])) {
                $base->where('l.id', 'eq', $param['id']);
            }
            if (!empty($param['mold_id'])) {
                $base->where('m.id', 'eq', $param['mold_id']);
            }
            if (!empty($param['mold_name'])) {
                $base->where('m.name', 'eq', $param['mold_name']);
            }
            if (!empty($param['title'])) {
                $base->where('l.title like "%' . $param['title'] . '%"');
            }
            if (!empty($param['dates'])) {
                list($date1, $date2) = explode(' - ', trim($param['dates']));
                $date3 = date('Y-m-d H:i:s', strtotime($date1));
                $date4 = date('Y-m-d H:i:s', strtotime($date2));
                if (!empty($date3) && !empty($date4)) {
                    $str  = "l.created_at between '" . $date3 . "' and '" . $date4 . "'";
                    $base->where($str);
                }
            }
        }

        public function find($param)
        {
            if (sfget_valid_one($param, array('id')) === false) {
                return $this->setErrorCode(100404)->setError('缺少必要参数！');
            }
            $base = Db::table(sfp('mold m'))
                ->join(sfp('list l'), 'm.id = l.mold_id')
                ->leftJoin(sfp('entity e'), 'e.type = "ad" and l.id = e.type_id')
                ->field(['m.name' => 'mold_name', 'l.*', 'e.data' => 'url', 'e.field1' => 'target', 'e.field2' => 'img', 'e.field3' => 'clicks', 'e.field4' => 'main']);
            $base->where('l.id', 'eq', $param['id']);
            $base->limit(1);
            $data = $base->find();
            return $data;
        }
        public function getAdById($id)
        {
            $data = $this->find(array('id' => $id));
            return $data;
        }


        public function findList($param)
        {
            $base = Db::table(sfp('mold m'))
                ->join(sfp('list l'), 'm.id = l.mold_id')
                ->leftJoin(sfp('entity e'), 'e.type = "ad" and l.id = e.type_id')
                ->field(['m.name' => 'mold_name', 'l.*', 'e.data' => 'url', 'e.field1' => 'target', 'e.field2' => 'img', 'e.field3' => 'clicks', 'e.field4' => 'main']);
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
            $base->order('sort', ' desc');
            $base->order('updated_at', 'desc');
            $list = $base->select();
            return $list;
        }

        public function findListTotal($param = array())
        {
            $base = Db::table(sfp('mold m'))
                ->join(sfp('list l'), 'm.id = l.mold_id');
            $this->bindWhere($base, $param);
            $total = $base->count('l.id');
            return $total;
        }

        public function isValid($param)
        {
            if (($key = sfis_valid($param, array('mold_id', 'title', 'url'))) !== true) {
                switch ($key) {
                    case 'mold_id':
                        return $this->setErrorCode(100500)->setError('请选择所属类目！');
                        break;
                }
                return $this->setErrorCode(100500)->setError($key . '参数找不到！');
            }
            return true;
        }

        public function addAd($param)
        {
            if (!$this->isValid($param)) {
                return false;
            }
            try {
                $this->begin();
                $bindList = array(
                    'mold_id' => $param['mold_id']
                    , 'title' => $param['title']
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s')
                );
                $result = Db::table(sfp('list'))->insert($bindList);
                if ($result) {
                    $lastId = Db::getLastInsID(sfp('list'));
                    $bindEntity = array(
                        'type' => 'ad'
                        , 'type_id' => $lastId
                        , 'field3' => 0//点击数
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
                    if (!isset($param['main'])) {
                        $param['main'] = 0;
                    }
                    $bindEntity['field4'] = $param['main'];
                    $result = Db::table(sfp('entity'))->insert($bindEntity);
                    if ($result) {
                        $this->commit();
                        return true;
                    } else {
                        throw new \Exception('插入广告失败！');
                    }
                }
            } catch (\Exception $e) {
                $this->rollBack();
                return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
            }
            return $this->setErrorCode(100400)->setError('未知错误！');
        }

        public function modifyAd($param)
        {
            if (empty($param['id'])) {
                return $this->setErrorCode(100404)->setError('缺少id参数！');
            }
            if (!$this->isValid($param)) {
                return false;
            }
            $id = $param['id'];
            $bindList = array();
            if (!empty($param['mold_id'])) {
                $bindList['mold_id'] = $param['mold_id'];
            }
            if (!empty($param['title'])) {
                $bindList['title'] = $param['title'];
            }
            try {
                $this->begin();
                if (!empty($bindList)) {
                    $data = Db::table(sfp('list'))->where('id', 'eq', $id)->value('id');
                    if (empty($data)) {
                        return $this->setErrorCode(100404)->setError('找不到广告数据！');
                    }
                    $bindList['updated_at'] = date('Y-m-d H:i:s');
                    Db::table(sfp('list'))->where('id', 'eq', $param['id'])->update($bindList);
                }
                if (sfget_valid_one($param, array('url', 'target', 'img', 'main')) !== false) {
                    $bindEntity = array('updated_at' => date('Y-m-d H:i:s'));
                    if (!empty($param['url'])) {
                        $bindEntity['data'] = $param['url'];
                    }
                    if (!empty($param['target'])) {
                        $bindEntity['field1'] = $param['target'];
                    }
                    if (!empty($param['img'])) {
                        $bindEntity['field2'] = $param['img'];
                    }
                    if (!isset($param['main'])) {
                        $param['main'] = 0;
                    }
                    $bindEntity['field4'] = $param['main'];
                    $result = Db::table(sfp('entity'))->where('type', 'eq', 'ad')->where('type_id', 'eq', $param['id'])->update($bindEntity);
                    if (!$result) {
                        throw new \Exception('更新广告属性失败！');
                    }
                }
                $this->commit();
                return true;
            } catch (\Exception $e) {
                $this->rollBack();
                return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
            }
        }

        public function deleteAdById($id)
        {
            try {
                $this->begin();
                Db::table(sfp('entity'))->where('type', 'eq', 'ad')->where('type_id', 'eq', $id)->delete();
                $result = Db::table(sfp('list'))->where('id', 'eq', $id)->delete();
                if ($result) {
                    $this->commit();
                    return true;
                } else {
                    throw new \Exception('删除广告失败！');
                }
            } catch (\Exception $e) {
                $this->rollBack();
                return $this->setErrorCode(100503)->setError('删除异常：' . $e->getMessage());
            }
        }
    }