<?php
    namespace app\api\model;
    
    use think\Db;
    
    class Base extends \app\BaseModel
    {
        protected $apiLastId = 0;
        public function validUserByToken($token)
        {
            $tokenList = Db::table(sfp('user'))->where('api_token', 'eq', $token)->select();
            if (empty($tokenList)) {
                return $this->setErrorCode(404)->setError('找不到该Token用户！');
            }
            if (count($tokenList) == 1) {
                $this->setAttr('user', $tokenList[0]);
                return true;
            } else {
                return $this->setErrorCode(503)->setError('出现多个相同Token用户！');
            }
        }

        public function findApi($param)
        {
            $base = Db::table(sfp('api'));
            $base->where('model', 'eq', $param['model']);
            $base->where('method', 'eq', $param['method']);
            if (!empty($param['key'])) {
                $base->where('param_key', 'eq', $param['key']);
            } else {
                ksort($param['param']);
                $base->where('param_md5', 'eq', md5(json_encode($param['param'])));
            }
            if (!empty($param['path'])) {
                $base->where('path', 'eq', $param['path']);
            }
            $base->where('expired_at', '>', date('Y-m-d H:i:s'));
            $base->limit(1);
            $base->order('id desc');
            $data = $base->find();
            return $data;
        }
        
        public function saveApi($param)
        {
            try{
                Db::startTrans();
                ksort($param['param']);
                $bind = array(
                    'model' => $param['model']
                    , 'method' => $param['method']
                    , 'param' => json_encode($param['param'])
                    , 'param_md5' => md5(json_encode($param['param']))
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s')
                );
                if (!empty($param['message'])) {
                    $bind['message'] = sfsubstr_ellipsis($param['message'], 196);
                }
                if (!empty($param['status'])) {
                    $bind['status'] = $param['status'];
                }
                if (!empty($param['path'])) {
                    $bind['path'] = $param['path'];
                }
                if (!empty($param['expired_at'])) {
                    $bind['expired_at'] = $param['expired_at'];
                } else {
                    $bind['expired_at'] = '2038-12-31 23:59:59';
                }
                if (isset($param['expired_count'])) {
                    $count = Db::table(sfp('api'))->where('param_md5', 'eq', $bind['param_md5'])->count('id');
                    if ($count >= $param['expired_count'] - 1) {
                        $bind['expired_at'] = '2038-01-01 00:00:00';
                    }
                }
                Db::table(sfp('api'))->insert($bind);
                $this->apiLastId = Db::table(sfp('api'))->getLastInsID();
                if (!empty($param['response'])) {
                    $bindData = array(
                        'api_id' => $this->apiLastId
                        , 'url' => $param['url']
                        , 'response' => $param['response']
                        , 'ip' => sfget_ip()
                        , 'created_at' => date('Y-m-d H:i:s')
                    );
                    Db::table(sfp('api_data'))->insert($bindData);
                    $this->setAttr('apiData', $bindData);
                }
                Db::commit();
                return true;
            } catch (\Exception $e) {
                Db::rollback();
            }
            return $this->setErrorCode(500)->setError('数据库插入异常！');
        }

        public function saveApiLastData($param)
        {
            try{
                Db::startTrans();
                $bind = array(
                    'updated_at' => date('Y-m-d H:i:s')
                );
                if (!empty($param['message'])) {
                    $bind['message'] = sfsubstr_ellipsis($param['message'], 196);
                }
                if (!empty($param['status'])) {
                    $bind['status'] = $param['status'];
                }
                if (!empty($param['expired_at'])) {
                    $bind['expired_at'] = $param['expired_at'];
                } else {
                    $bind['expired_at'] = '2038-12-31 23:59:59';
                }
                if (isset($param['expired_count'])) {
                    $count = Db::table(sfp('api'))->where('param_md5', 'eq', $bind['param_md5'])->count('id');
                    if ($count >= $param['expired_count'] - 1) {
                        $bind['expired_at'] = '2038-01-01 00:00:00';
                    }
                }
                if (!empty($this->apiLastId)) {
                    Db::table(sfp('api'))->where('id', 'eq', $this->apiLastId)->update($bind);
                    if (!empty($param['response'])) {
                        $bindData = array(
                            'api_id' => $this->apiLastId
                            , 'url' => $param['url']
                            , 'response' => $param['response']
                            , 'ip' => sfget_ip()
                            , 'created_at' => date('Y-m-d H:i:s')
                        );
                        Db::table(sfp('api_data'))->insert($bindData);
                        $this->setAttr('apiData', $bindData);
                    }
                    Db::commit();
                    return true;
                } else {
                    return $this->setErrorCode(500)->setError('没有找到最后接口数据！');
                }
            } catch (\Exception $e) {
                Db::rollback();
            }
            return $this->setErrorCode(500)->setError('数据库更新异常！');
        }
    }