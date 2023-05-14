<?php

namespace app\base\model;
use think\Db;

class Session extends \app\BaseModel
{
    private $account;
    public function __construct($data = [])
    {
        @session_start();
        parent::__construct($data);
        $sessionId = session_id();
        $this->account = Db::table(sfp('account'))->where('session_id', 'eq', $sessionId)->limit(1)->find();
        $_SESSION['base_info'] = isset($_SESSION['base_info']) ? $_SESSION['base_info'] : [];
    }

    /**
     * 获取会话者经度
     */
    public function getLon() 
    {
        if (isset($_SESSION['base_info']['lon'])) {
            return $_SESSION['base_info']['lon'];
        }
        return 0;
    }

    public function setLon($lon)
    {
        $_SESSION['base_info']['lon'] = $lon;
    }

    /**
     * 获取会话者纬度
     */
    public function getLat()
    {
        if (isset($_SESSION['base_info']['lat'])) {
            return $_SESSION['base_info']['lat'];
        }
        return 0;
    }

    public function setLat($lat)
    {
        $_SESSION['base_info']['lat'] = $lat;
    }
    
    public function getName() 
    {
        if ($this->account && !empty($this->account['name'])) {
            return $this->account['name'];
        }
        return '';
    }
    
    public function getTelephone()
    {
        if ($this->account && !empty($this->account['telephone'])) {
            return $this->account['telephone'];
        }
        return '';
    }

    public function getId()
    {
        if (empty($this->account)) {
            $this->saveSession();
        }
        return $this->account['id'];
    }
    
    public function saveInfo($param)
    {
        $bind = [];
        if (!empty($param['name'])) {
            $_SESSION['base_info']['name'] = $param['name'];
            $bind['name'] = $param['name'];
        }
        if (!empty($param['telephone'])) {
            $_SESSION['base_info']['telephone'] = $param['telephone'];
            $bind['telephone'] = $param['telephone'];
        }
        if (!empty($bind)) {
            $qty = Db::table(sfp('account'))->where('session_id', 'eq', session_id())->update($bind);
            if ($qty) {
                return true;
            } else {
                return $this->saveSession($param);
            }
        } else {
            return true;
        }
    }
    
    public function saveSession($param = array())
    {
        if (!isset($param['session_id'])) {
            $sessionId = session_id();
        } else {
            session_id($param['session_id']);
            $sessionId = $param['session_id'];
        }
        $oldSession = $_SESSION;
        $time = time();
        if (!empty($oldSession['updated_last'])) {
            if ($time - $oldSession['updated_last'] < 60) {
                return true;
            }
        }
        $_SESSION['updated_last'] = $time;
        $oldSession['updated_last'] = $time;
        $exists = Db::table(sfp('account'))->where('session_id', 'eq', $sessionId)->limit(1)->find();
        if (!empty($exists)) {
            $bind = array(
                'updated_at' => date('Y-m-d H:i:s')
                , 'updated_lon' => $this->getLon()
                , 'updated_lat' => $this->getLat()
                , 'updated_ip' => sfget_ip()
            );
            if (!empty($param['name'])) {
                $bind['name'] = $param['name'];
            }
            if (!empty($param['telephone'])) {
                $bind['telephone'] = $param['telephone'];
            }
            Db::table(sfp('account'))->where('id', 'eq', $exists['id'])->update($bind);
        } else {
            try {
                $bind = array(
                    'session_id' => $sessionId
                    , 'created_at' => date('Y-m-d H:i:s')
                    , 'updated_at' => date('Y-m-d H:i:s')
                    , 'updated_lon' => $this->getLon()
                    , 'updated_lat' => $this->getLat()
                    , 'updated_ip' => sfget_ip()
                );
                if (!empty($param['name'])) {
                    $bind['name'] = $param['name'];
                }
                if (!empty($param['telephone'])) {
                    $bind['telephone'] = $param['telephone'];
                }
                Db::table(sfp('account'))->insert($bind);
            } catch (\Exception $e) {
                session_regenerate_id(true);
                $sessionId = session_id();
                $param['session_id'] = $sessionId;
                $_SESSION = $oldSession;
                setcookie('PHPSESSID', $sessionId, time() + 3156000, '/');
                $this->saveSession($param);
            }
        }
        return true;
    }
}