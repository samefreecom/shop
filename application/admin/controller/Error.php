<?php
namespace app\admin\controller;


use think\facade\Request;

class Error
{
    public function _empty()
    {
        define('FALG_ADMIN', true);
        $model = Request::controller();
        $class = "app\\" . $model . "\\controller\\Admin";
        if (class_exists($class)) {
            $obj = new $class();
            $action = Request::action();
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
