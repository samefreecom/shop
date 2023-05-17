<?php
    namespace app\cms\model;
    use app\BaseModel;
    use think\Db;
    class Clist extends BaseModel
    {
        private function compressHtml($string)
        {
            $string=str_replace("\r\n",'',$string);//清除换行符
            $string=str_replace("\n",'',$string);//清除换行符
            $string=str_replace("\t",'',$string);//清除制表符
            $pattern=array(
                "/> *([^ ]*) *</",//去掉注释标记
                "/[\s]+/",
                "/<!--[^!]*-->/",
                "/\" /",
                "/ \"/",
                "'/\*[^*]*\*/'"
            );
            $replace=array (
                ">\\1<",
                " ",
                "",
                "\"",
                "\"",
                ""
            );
            return preg_replace($pattern, $replace, $string);
        }

        public function bindWhere(&$base, $param = array())
        {
            if (!empty($param['id'])) {
                $base->where('l.id', 'eq', $param['id']);
            }
            if (!empty($param['type'])) {
                $base->where('m.type', 'eq', $param['type']);
            }
            if (!empty($param['mold_id'])) {
                $base->where('m.id', 'eq', $param['mold_id']);
            }
            if (!empty($param['mold_name'])) {
                $base->where('m.name', 'eq', $param['mold_name']);
            }
            if (!empty($param['parent_name'])) {
                $param['parent_id'] = Db::table(sfp('mold'))->where('name', 'eq', $param['parent_name'])->value('id');
            }
            if (!empty($param['parent_id'])) {
                $base->where('m.path like "%,' . $param['parent_id'] . ',%"');
            }
            if (!empty($param['title'])) {
                $base->where('l.title like "%' . $param['title'] . '%"');
                $base->where('e.type', 'eq', 'list');
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

        public function validFindList($param = array())
        {
            if (sfget_valid_one($param, array('mold_id', 'parent_name', 'mold_name', 'type', 'title', 'dates')) === false) {
                return $this->setErrorCode(100404)->setError('缺少必要参数！');
            }
            return true;
        }

        public function findList($param = array())
        {
            if (empty($param['limit'])) {
                return $this->setErrorCode(100500)->setError('不支持所有查询！');
            }
            if (!$this->validFindList($param)) {
                return false;
            }
            $base = Db::table(sfp('mold m'))
                ->join(sfp('list l'), 'm.id = l.mold_id')
                ->leftJoin(sfp('url u'), 'u.type = "list" and l.id = u.type_id');
            if (isset($param['use_content'])) {
                $base->leftJoin(sfp('entity e'), 'e.type = "list" and l.id = e.type_id')
                    ->field(['m.name' => 'mold_name', 'l.*', 'e.data' => 'content',  'u.value' => 'url']);
            } else {
                $base->field(['m.name' => 'mold_name', 'l.*', 'u.value' => 'url']);
            }
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
            if (!$this->validFindList($param)) {
                return false;
            }
            $base = Db::table(sfp('mold m'))
                ->join(sfp('list l'), 'm.id = l.mold_id');
            $this->bindWhere($base, $param);
            $total = $base->count('l.id');
            return $total;
        }

        public function addList($param)
        {
            if (sfis_valid($param, array('title', 'content')) !== true) {
                return $this->setErrorCode(100404)->setError('标题或描述不可为空！');
            }
            try {
                $this->begin();
                $listBind = array(
                    'mold_id' => 0
                    , 'sort' => 0
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s')
                );
                sfarray_push($listBind, $param, array('mold_id', 'title', 'outline'));
                if (!empty($param['img'])) {
                    $listBind['preview_path'] = $param['img'];
                }
                $result = Db::table(sfp('list'))->insert($listBind);
                if ($result) {
                    $id = Db::getLastInsID(sfp('mold'));
                    if (!empty($param['url'])) {
                        $url = new Url();
                        if (!$url->saveUrl('list', $id, $param['url'])) {
                            throw new \Exception($url->getError());
                        }
                    }
                    $entityBind = array(
                        'type' => 'list'
                        , 'type_id' => $id
                        , 'data' => $param['content']
                        , 'created_at' => date('Y-m-d H:i:s')
                        , 'updated_at' => date('Y-m-d H:i:s')
                    );
                    if (!empty($param['compress'])) {
                        $entityBind['data'] = $this->compressHtml($entityBind['data']);
                    }
                    $ret = Db::table(sfp('entity'))->insert($entityBind);
                    if ($ret) {
                        $this->commit();
                        return true;
                    } else {
                        throw new \Exception('插入描述失败！');
                    }
                }
            } catch (\Exception $e) {
                $this->rollBack();
                return $this->setException($e);
            }
            return $this->setErrorNull();
        }


        public function modifyList($param)
        {
            if (empty($param['id'])) {
                return $this->setErrorCode(100404)->setError('缺少id参数！');
            }
            if (sfis_valid($param, array('title', 'content')) !== true) {
                return $this->setErrorCode(100404)->setError('标题或描述不可为空！');
            }
            $id = $param['id'];
            $listBind = array(
                'updated_at' => date('Y-m-d H:i:s')
            );
            sfarray_push($listBind, $param, array('mold_id', 'title', 'outline'));
            if (!empty($param['img'])) {
                $listBind['preview_path'] = $param['img'];
            }
            try {
                $this->begin();
                if (!empty($listBind)) {
                    $data = Db::table(sfp('list'))->where('id', 'eq', $id)->value('id');
                    if (empty($data)) {
                        return $this->setErrorCode(100404)->setError('找不到数据！');
                    }
                    Db::table(sfp('list'))->where('id', 'eq', $param['id'])->update($listBind);
                }
                $url = new Url();
                if (!empty($param['url']) || $url->getUrlValue('list', $id) != '') {
                    if (!$url->saveUrl('list', $id, $param['url'])) {
                        throw new \Exception($url->getError());
                    }
                }
                if (sfget_valid_one($param, array('content')) !== false) {
                    $entityBind = array('updated_at' => date('Y-m-d H:i:s'));
                    $entityBind['data'] = $param['content'];
                    if (!empty($param['compress'])) {
                        $entityBind['data'] = $this->compressHtml($entityBind['data']);
                    }
                    $result = Db::table(sfp('entity'))->where('type', 'eq', 'list')->where('type_id', 'eq', $param['id'])->update($entityBind);
                    if (!$result) {
                        throw new \Exception('更新描述失败！');
                    }
                }
                $this->commit();
                return true;
            } catch (\Exception $e) {
                $this->rollBack();
                return $this->setErrorCode(100503)->setError('保存异常：' . $e->getMessage());
            }
        }

        public function find($param)
        {
            if (sfget_valid_one($param, array('id', 'title')) === false) {
                return $this->setErrorCode(100404)->setError('缺少必要参数！');
            }
            $base = Db::table(sfp('mold m'))
                ->join(sfp('list l'), 'm.id = l.mold_id')
                ->leftJoin(sfp('url u'), 'u.type = "list" and l.id = u.type_id')
                ->leftJoin(sfp('entity e'), 'e.type = "list" and l.id = e.type_id')
                ->field(['m.name' => 'mold_name', 'l.*', 'e.data' => 'content',  'u.value' => 'url']);
            $this->bindWhere($base, $param);
            $base->limit(1);
            $data = $base->find();
            return $data;
        }

        public function getById($id)
        {
            return $this->find(array('id' => $id));
        }

        public function getPrevById($id)
        {
            $data = Db::table(sfp('list'))->where('id', 'eq', $id)->limit(1)->find();
            if (!empty($data)) {
                $prevData = Db::table(sfp('list'))->where('id', '<', $id)->where('mold_id', 'eq', $data['mold_id'])->order('id desc')->limit(1)->find();
                if (!empty($prevData)) {
                    return $prevData;
                }
            }
            return null;
        }

        public function getNextById($id)
        {
            $data = Db::table(sfp('list'))->where('id', 'eq', $id)->limit(1)->find();
            if (!empty($data)) {
                $nextData = Db::table(sfp('list'))->where('id', '>', $id)->where('mold_id', 'eq', $data['mold_id'])->limit(1)->order('id asc')->find();
                if (!empty($nextData)) {
                    return $nextData;
                }
            }
            return null;
        }

        public function deleteListById($id)
        {
            Db::table(sfp('url'))->where('type', 'eq', 'list')->where('type_id', 'eq', $id)->delete();
            Db::table(sfp('entity'))->where('type', 'eq', 'list')->where('type_id', 'eq', $id)->delete();
            Db::table(sfp('list'))->where('id', 'eq', $id)->delete();
            return true;
        }
    }