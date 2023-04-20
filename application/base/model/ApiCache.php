<?php
namespace app\base\model;

use app\BaseModel;
use think\Db;

class ApiCache extends BaseModel
{
    public function getCache($type, $url, $param)
    {
        $key = md5(json_encode($param));
        $data = Db::table(sfp('api_cache'))->where('type', 'eq', $type)->where('url', 'eq', $url)->where('key', 'eq', $key)->order('id desc')->limit(1)->find();
        if (!empty($data)) {
            return json_decode($data['data'], true);
        }
        return null;
    }

    public function saveCache($type, $url, $param, $data)
    {
        $key = md5(json_encode($param));
        $bind = array(
            'type' => $type
            , 'url' => $url
            , 'key' => $key
            , 'data' => json_encode($data)
            , 'created_at' => date('Y-m-d H:i:s')
        );
        $result = Db::table(sfp('api_cache'))->insert($bind);
        if ($result) {
            return true;
        }
        return $this->setErrorCode(503)->setError('数据库插入错误！');
    }
}