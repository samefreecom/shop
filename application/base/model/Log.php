<?php
namespace app\base\model;

use think\Db;

class Log extends \app\BaseModel
{
    private $userID = 0;

    private function hashMod($key, $mod = 100)
    {
        $code = crc32($key);
        return abs($code) % $mod;
    }

    private function initTable($key) {
        $tbl = sfp('log_' . $key);
        try {
            $sql = "CREATE TABLE `" . $tbl . "` (`id` bigint(20) NOT NULL AUTO_INCREMENT,`object` char(1) DEFAULT 'D' COMMENT '日志对象',`level` char(1) NOT NULL DEFAULT 'D' COMMENT '日志等级',`type` varchar(50) NOT NULL COMMENT '日志类型',`no` varchar(200) NOT NULL COMMENT '日志编码',`content` varchar(500) NOT NULL COMMENT '日志内容',`created_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建者',`ip` varchar(50) NOT NULL,`created_at` datetime NOT NULL COMMENT '创建时间',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
            Db::execute($sql);
        } catch (\Exception $e) {}
        try {
            $tbl = sfp('log_data_' . $key);
            $sql = "CREATE TABLE `" . $tbl . "` (`id` bigint(20) NOT NULL AUTO_INCREMENT,`log_id` bigint(20) NOT NULL COMMENT '日志编号',`data` text NOT NULL COMMENT '日志数据',`created_at` datetime NOT NULL COMMENT '创建时间',PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
            Db::execute($sql);
        } catch (\Exception $e) {}
        return true;
    }

    private function insertTable($key, $bind)
    {
        $data = null;
        if (isset($bind['data'])) {
            $data = $bind['data'];
            unset($bind['data']);
        }
        $tbl = sfp('log_' . $key);
        $result = Db::table($tbl)->insert($bind);
        if ($result) {
            if (!empty($data)) {
                $lastId = Db::getLastInsID();
                $bindData = array(
                    'log_id' => $lastId
                    , 'data' => $data
                    , 'created_at' => date('Y-m-d H:i:s')
                );
                Db::table(sfp('log_data_' . $key))->insert($bindData);
            }
            return true;
        }
        return $this->setErrorCode(404)->setError('找不到日志表');
    }

    public function add($param)
    {
        $bind = array(
            'type' => $param['type']
            , 'level' => strtoupper($param['level'])
            , 'no' => $param['no']
            , 'content' => $param['content']
            , 'created_id' => $this->userID
            , 'ip' => sfget_ip()
        );
        if (!empty($param['created_at'])) {
            $bind['created_at'] = $param['created_at'];
        } else {
            $bind['created_at'] = date('Y-m-d H:i:s');
        }
        if (!empty($param['object'])) {
            $bind['object'] = $param['object'];
        }
        if (!empty($param['data'])) {
            $data = $param['data'];
            if (is_array($data) || is_object($data)) {
                $data = serialize($data);
            }
            $bind['data'] = $data;
        }
        $key = $this->hashMod($param['no']);
        try {
            return $this->insertTable($key, $bind);
        } catch (\Exception $e) {
            $this->initTable($key);
            return $this->insertTable($key, $bind);
        }
    }

    public function copy($oldType, $oldNo, $newType, $newNo)
    {
        $listOld = $this->findListByTypeAndNo($oldType, $oldNo);
        for ($i = count($listOld) - 1; $i >= 0; $i--) {
            $log = $listOld[$i];
            $newParam = array(
                'type' => $newType
                , 'level' => $log['level']
                , 'no' => $newNo
                , 'content' => $log['content']
                , 'object' => $log['object']
                , 'created_at' => $log['created_at']
            );
            //TODO 未克隆数据，需要额外查询
            if (!empty($log['data'])) {
                $newParam['data'] = unserialize($log['data']);
            }
            $this->add($newParam);
        }
    }

    public function findListByTypeAndNo($type, $no)
    {
        $key = $this->hashMod($no);
        $list = null;
        try {
            $base = Db::table(sfp('log_' . $key))->where('type', 'eq', $type)->where('no', 'eq', $no);
            $base->order('id desc');
            $list = $base->select();
        } catch (\Exception $e) {
        }
        return $list;
    }
}