<?php
namespace app\admin\controller;


use think\facade\Request;

class Item
{
    public function _empty()
    {
        define('FALG_ADMIN', true);
        $model = Request::action();
        $class = "app\\" . $model . "\\controller\\Admin";
        if (class_exists($class)) {
            $obj = new $class();
            $param = Request::param();
            $firstKey = key($param);
            $action = basename($firstKey);
            if (empty($action) || $model == $action) {
                $action = 'index';
            }
            if (method_exists($obj, $action)) {
                return $obj->$action();
            } else {
                sfquit('not found action:' . $action);
            }
        } else {
            sfquit('not found model:' . $model);
        }
    }
}
