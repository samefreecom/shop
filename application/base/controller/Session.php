<?php
    namespace app\base\controller;

    use think\facade\Request;

    class Session
    {
        public function saveLocation()
        {
            $param = Request::param();
            $mSession = new \app\base\model\Session();
            /*
            if (isset($param['cookieid'])) {
                $cookieid = sfdestr($param['cookieid']);
                if (!empty($cookieid)) {
                    session_id($cookieid);
                }
            }
            */
            if (isset($param['lon'])) {
                $mSession->setLon($param['lon']);
            }
            if (isset($param['lat'])) {
                $mSession->setLat($param['lat']);
            }
            sfresponse(1);
        }
        
        public function saveInfo()
        {
            $param = Request::param();
            $mSession = new \app\base\model\Session();
            if (!$mSession->saveInfo($param)) {
                sfresponse(0, $mSession->getError());
            }
            sfresponse(1);
        }
    }