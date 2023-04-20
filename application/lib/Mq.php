<?php
use think\Db;
class Lib_Mq extends Lib_Base
{
    private $_session_id;//会话编号
    private $_lastList;

    /**
     * 设置会话编号
     * @param $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->_session_id = $sessionId;
    }

    public function clearTask()
    {
        $base = Db::table(sfp('queue'))->where('session_id', 'neq', '')->where('updated_at', 'lt', date('Y-m-d H:i:s', strtotime('-2 hour')));
        $list = $base->select();
        if (!empty($list)) {
            $bindList = [];
            $ids = [];
            foreach ($list as $value) {
                $ids[] = $value['id'];
                unset($value['id']);
                $value['status'] = 2;
                $bindList[] = $value;
            }
            if (!empty($bindList)) {
                if (Db::table(sfp('queue_log'))->insertAll($bindList)) {
                    Db::table(sfp('queue'))->where('id', 'in', $ids)->where('session_id', 'neq', '')->where('updated_at', 'lt', date('Y-m-d H:i:s', strtotime('-2 hour')))->delete();
                }
            }
        }
    }

    public function initTask()
    {
        $reqList = array(
            array(
                'exchange' => 'path'
                , 'routing_key' => '\app\base\model\Event-sync'
                , 'start_hour' => 0
                , 'source_code' => 'MQ_INIT'
                , 'exec_ip' => '43.153.26.120'
            )
            , array(
                'exchange' => 'path'
                , 'routing_key' => '\app\base\model\Event-sync'
                , 'start_hour' => 0
                , 'source_code' => 'MQ_INIT'
                , 'exec_ip' => '43.155.130.134'
            )
            , array(
                'exchange' => 'path'
                , 'routing_key' => '\app\track\model\Api-syncTask'
                , 'start_hour' => 12
                , 'source_code' => 'MQ_INIT'
            )
            , array(
                'exchange' => 'path'
                , 'routing_key' => '\app\tracking\model\Api-syncTask'
                , 'start_hour' => 3
                , 'source_code' => 'MQ_INIT'
            )
            , array(
                'exchange' => 'path'
                , 'routing_key' => '\app\order\model\Task-sync'
                , 'start_hour' => 2
                , 'source_code' => 'MQ_INIT'
            )
            , array(
                'exchange' => 'path'
                , 'routing_key' => '\app\idcard\model\Task-sync'
                , 'start_hour' => 0
                , 'source_code' => 'MQ_INIT'
                , 'exec_ip' => '43.153.26.120'
            )
            , array(
                'exchange' => 'path'
                , 'routing_key' => '\app\idcard\model\Task-sync'
                , 'start_hour' => 0
                , 'source_code' => 'MQ_INIT'
                , 'exec_ip' => '43.155.130.134'
            )
        );
        $this->clearTask();
        foreach ($reqList as $key => $value) {
            $base = Db::table(sfp('queue'))->where('exchange', 'eq', $value['exchange'])->where('routing_key', 'eq', $value['routing_key']);
            if (!empty($value['exec_ip'])) {
                $ip = sfget_ip();
                if (!in_array($ip, array('127.0.0.1', '::1', $value['exec_ip']))) {
                    continue;
                }
                $base->where('exec_ip', 'eq', $value['exec_ip']);
            }
            $id = $base->limit(1)->value('id');
            if (empty($id)) {
                if (!isset($value['body'])) {
                    $value['body'] = '';
                }
                $value['created_at'] = date('Y-m-d H:i:s');
                $value['updated_at'] = date('Y-m-d H:i:s');
                Db::table(sfp('queue'))->insert($value);
            }
        }
    }

    /**
     * 发布消息
     * @param string $exchange  交换机名
     * @param string $routingKey    路由名
     * @param string $props     附加参数
     * @param string $body  消息体
     * @param int $startHour  最小启动小时
     * @param string $execIp  可运行的服务器IP
     * @param string $sourceCode  来源代码
     * @param string $startAt  开始时间
     * @return bool 是否发布成功
     */
    public function publish($exchange, $routingKey, $props, $body, $startHour = 0, $execIp = '', $sourceCode = 'MQ', $startAt = null)
    {
        if (empty($startAt)) {
            $startAt =  date('Y-m-d H:i:s');
        }
        $bind = array(
            'exchange' => $exchange
        , 'routing_key' => $routingKey
        , 'props' => $props
        , 'body' => $body
        , 'start_hour' => $startHour
        , 'exec_ip' => $execIp
        , 'source_code' => $sourceCode
        , 'status' => 0
        , 'created_at' => $startAt
        , 'updated_at' => date('Y-m-d H:i:s')
        );
        $result = Db::table(sfp('queue'))->insert($bind);
        if ($result) {
            return $result;
        }
        return false;
    }

    public function saveTask($exchange, $routingKey, $props, $body, $startHour = 0, $execIp = '', $sourceCode = 'MQ', $startAt = null)
    {
        $id = Db::table(sfp('queue'))->where('exchange', 'eq', $exchange)->where('routing_key', 'eq', $routingKey)->where('session_id', 'eq', "")->where('exec_ip', 'eq', $execIp)->limit(1)->value('id');
        if (empty($id)) {
            return $this->publish($exchange, $routingKey, $props, $body, $startHour, $execIp, $sourceCode, $startAt);
        } else {
            return true;
        }
    }

    public function batch($exchange, $routingKey, $props, $bodyList, $startHour = 0, $execIp = '', $sourceCode = 'MQ', $startAt = null)
    {
        if (empty($startAt)) {
            $startAt =  date('Y-m-d H:i:s');
        }
        $bindList = array();
        foreach ($bodyList as $value) {
            $bindList[] = array(
                'exchange' => $exchange
            , 'routing_key' => $routingKey
            , 'props' => $props
            , 'body' => $value
            , 'start_hour' => $startHour
            , 'exec_ip' => $execIp
            , 'source_code' => $sourceCode
            , 'status' => 0
            , 'created_at' => $startAt
            , 'updated_at' => date('Y-m-d H:i:s')
            );
        }
        if (!empty($bindList)) {
            return Db::table(sfp('queue'))->insertAll($bindList);
        }
        return false;
    }

    public function getList($exchange, $tag = null, $limit = 5)
    {
        $this->_lastList = array();
        if (!empty($tag)) {
            $this->_session_id = $tag;
        }
        if (empty($this->_session_id)) {
            $this->_session_id = sfget_now_time_long_number();
        }
        //占用任务
        $sql = sprintf('UPDATE %s SET session_id = "%s", updated_at = "' . date('Y-m-d H:i:s') .  '" WHERE status = 0 AND session_id = "" AND DATE_ADD(created_at, INTERVAL start_hour HOUR) <= "' . date('Y-m-d H:i:s') . '" AND exec_ip IN ("' . sfget_ip()   . '", "") %s ORDER BY id ASC LIMIT ' . $limit, sfp('queue'), $this->_session_id, ' AND exchange="' . $exchange . '"');
        $result = Db::execute($sql);
        if ($result) {
            $sessionId = $this->_session_id;
            $list = Db::table(sfp('queue'))->where('session_id', 'eq', $sessionId)->select();
            $this->_lastList = $list;
            return $list;
        }
        return null;
    }

    public function update($id, $newData)
    {
        foreach ($this->_lastList as $key => $value) {
            if ($value['id'] == $id) {
                $this->_lastList[$key] = array_merge($this->_lastList[$key], $newData);
                return true;
            }
        }
        return null;
    }

    public function wait()
    {
        if (!empty($this->_lastList)) {
            Db::startTrans();
            $sessionId = $this->_lastList[0]['session_id'];
            $this->update(array('status' => 1, 'updated_at' => date('Y-m-d H:i:s')), array('session_id' => $sessionId));
            foreach ($this->_lastList as $value) {
                $logBind = $value;
                $logBind['status'] = 1;
                $logBind['updated_at'] = date('Y-m-d H:i:s');
                unset($logBind['id']);
                $result = Db::table(sfp('queue_log'))->insert($logBind);
                if ($result) {
                    Db::table(sfp('queue'))->where('id', 'eq', $value['id'])->delete();
                } else {
                    Db::rollback();
                    break;
                }
            }
            Db::commit();
            return true;
        }
        return null;
    }

    private function execQueue($value, $remove = true)
    {
        switch ($value['exchange']) {
            case 'event':
                $log = '';
                try {
                    $eventName = $value['routing_key'];
                    sftrigger_event($eventName, 0, $value);
                    $errors = sfget_event_exception_list($eventName);
                    if (empty($errors)) {
                        $value['status'] = 1;
                    } else {
                        $value['status'] = 0;
                        foreach ($errors as $e) {
                            $log .= $e->getMessage() . "\r\n";
                        }
                    }
                } catch (\Exception $e) {
                    $errArr = explode('#', $e->getTraceAsString());
                    $errArrLen = count($errArr);
                    $log .= "异常：" . $e->getMessage();
                    for ($i = 1; $i < $errArrLen; $i++) {
                        list($line) = explode(': ', $errArr[$i]);
                        $log .= ("\r\n" . str_replace(ROOT, '', $line));
                    }
                }
                $logBind['log'] = sfsubstr_ellipsis($log, 496);
                $logBind = $value;
                unset($logBind['id']);
                $result = Db::table(sfp('queue_log'))->insert($logBind);
                if ($result) {
                    if ($remove) {
                        Db::table(sfp('queue'))->where('id', 'eq', $value['id'])->delete();
                    }
                    return true;
                }
                break;
            case 'path':
                $log = '';
                $result = false;
                try {
                    if (is_numeric(strpos($value['routing_key'], '-'))) {
                        list($class, $fun) = explode('-', $value['routing_key']);
                        $object = new $class();
                        if (!empty($object)) {
                            if (method_exists($object, $fun)) {
                                if (!empty($value['body'])) {
                                    $json = json_decode($value['body'], true);
                                    if (!empty($json) && is_array($json)) {
                                        $isNumber = true;
                                        foreach ($json as $k => $v) {
                                            if (!is_numeric($k)) {
                                                $isNumber = false;
                                                break;
                                            }
                                        }
                                        if ($isNumber) {
                                            call_user_func_array(array($object, $fun), $json);
                                        } else {
                                            call_user_func_array(array($object, $fun), array($value['body']));
                                        }
                                    } elseif (is_numeric(strpos($_POST['param'], "\r\n"))) {
                                        call_user_func_array(array($object, $fun), array(parse_ini_string($value['body'])));
                                    } else {
                                        call_user_func_array(array($object, $fun), array($value['body']));
                                    }
                                } else {
                                    $object->$fun();
                                }
                            } else {
                                $log .= "不方法：" . $fun . "\r\n";
                            }
                        } else {
                            $log .= "不存在：" . $class . "类" . "\r\n";
                        }
                    }
                } catch (\Exception $e) {
                    $errArr = explode('#', $e->getTraceAsString());
                    $errArrLen = count($errArr);
                    $log .= "异常：" . $e->getMessage();
                    for ($i = 1; $i < $errArrLen; $i++) {
                        list($line) = explode(': ', $errArr[$i]);
                        $log .= ("\r\n" . str_replace(ROOT, '', $line));
                    }
                }
                if (!empty($object)) {
                    if (method_exists($object, 'getErrorCode')) {
                        $errorCode = $object->getErrorCode();
                        if (empty($errorCode)) {
                            $result = true;
                        } else {
                            if (empty($log)) {
                                $log = $object->getError();
                            }
                        }
                    }
                }
                if ($result) {
                    $value['status'] = 1;
                } else {
                    $value['status'] = 0;
                }
                $logBind = $value;
                $logBind['log'] = sfsubstr_ellipsis($log, 496);
                unset($logBind['id']);
                $result = Db::table(sfp('queue_log'))->insert($logBind);
                if ($result) {
                    if ($remove) {
                        Db::table(sfp('queue'))->where('id', 'eq', $value['id'])->delete();
                    }
                    return true;
                }
                break;
            case 'export':
                $log = '';
                $result = false;
                try {
                    if (is_numeric(strpos($value['routing_key'], '-'))) {
                        list($class, $fun) = explode('-', $value['routing_key']);
                        $object = new $class();
                        if (!empty($object)) {
                            if (method_exists($object, $fun)) {
                                list($start, $limit) = explode('-', $value['props']);
                                if (!empty($value['body'])) {
                                    if (!empty($limit)) {
                                        $sql = $value['body'] . ' limit ' . $start . ',' . ($limit - $start);
                                        $list = Db::execute($sql);
                                        try {
                                            $result = $object->$fun($list, $value);
                                            if (!is_bool($result)) {
                                                $value['data'] = json_encode($result);
                                                $result = true;
                                            }
                                        } catch (Exception $e) {
                                            $result = false;
                                            $log .= $e->getMessage() . "\r\n";
                                        }
                                    } else {
                                        $log .= "找不到分页参数" . "\r\n";
                                    }
                                } else {
                                    $log .= "找不到导出SQL" . "\r\n";
                                }
                                $log .= print_r($result, true) . "\r\n";
                            } else {
                                $log .= "不方法：" . $fun . "\r\n";
                            }
                        } else {
                            $log .= "不存在：" . $class . "类" . "\r\n";
                        }
                    }
                } catch (\Exception $e) {
                    $errArr = explode('#', $e->getTraceAsString());
                    $errArrLen = count($errArr);
                    $log .= "异常：" . $e->getMessage();
                    for ($i = 1; $i < $errArrLen; $i++) {
                        list($line) = explode(': ', $errArr[$i]);
                        $log .= ("\r\n" . str_replace(ROOT, '', $line));
                    }
                }
                if (!empty($object)) {
                    if (method_exists($object, 'getErrorCode')) {
                        $errorCode = $object->getErrorCode();
                        if (empty($errorCode)) {
                            $result = true;
                        } else {
                            if (empty($log)) {
                                $log = $object->getError();
                            }
                        }
                    }
                }
                if ($result) {
                    $value['status'] = 1;
                } else {
                    $value['status'] = 0;
                }
                $logBind = $value;
                $logBind['log'] = sfsubstr_ellipsis($log, 496);
                $logBind['updated_at'] = date('Y-m-d H:i:s');
                unset($logBind['id']);
                $result = Db::table(sfp('queue_log'))->insert($logBind);
                if ($result) {
                    if ($remove) {
                        Db::table(sfp('queue'))->where('id', 'eq', $value['id'])->delete();
                    }
                    return true;
                }
                break;
        }
        return false;
    }

    //消费
    public function execTask($exchange, $limit = 5)
    {
        $queryQty = 0;
        $execQty = 0;
        if (empty($this->_session_id)) {
            $this->_session_id = sfget_now_time_long_number();
        }
        //占用任务
        $sql = sprintf('UPDATE %s SET session_id = "%s", updated_at = "' . date('Y-m-d H:i:s') .  '" WHERE status = 0 AND session_id = "" AND DATE_ADD(created_at, INTERVAL start_hour HOUR) <= "' . date('Y-m-d H:i:s') . '" AND exec_ip IN ("' . sfget_ip()   . '", "") %s ORDER BY id ASC LIMIT ' . $limit, sfp('queue'), $this->_session_id, ' AND exchange="' . $exchange . '"');
        $result = Db::execute($sql);
        if ($result) {
            $sessionId = $this->_session_id;
            $list = Db::table(sfp('queue'))->where('session_id', 'eq', $sessionId)->select();
            if (!empty($list)) {
                $queryQty = count($list);
                foreach ($list as $value) {
                    if ($this->execQueue($value)) {
                        $execQty++;
                    }
                }
            }
        }
        return $queryQty . ':' . $execQty;
    }

    public function execById($id)
    {
        if (empty($this->_session_id)) {
            $this->_session_id = sfget_now_time_long_number();
        }
        Db::table(sfp('queue'))->where('id', 'eq', $id)->update(array('session_id' => $this->_session_id, 'updated_at' => date('Y-m-d H:i:s')));
        $queue = Db::table(sfp('queue'))->where('id', 'eq', $id)->find();
        if (!empty($queue)) {
            return $this->execQueue($queue, false);
        }
        return 'not found queue';
    }

    public function publishEventTask($name, $param)
    {
        $this->publish('event', $name, '', is_array($param) ? json_encode($param) : $param);
    }

    public function publishPathTask($path, $param)
    {
        $this->publish('path', $path, '', is_array($param) ? json_encode($param) : $param);
    }

    public function publishExportTask($callbackPath, $sql, $limit, $total)
    {
        for ($i = 0; $i < $total; $i += $limit) {
            $this->publish('export', $callbackPath, $i . '-' . ($i + $limit), $sql);
        }
    }
}