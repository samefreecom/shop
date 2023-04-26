<?php
namespace app\admin\controller;

use think\facade\Request;

class Login
{
    public function index()
    {
        define('FALG_ADMIN', true);
        if (Request::isPost()) {
            @session_start();
            if (isset($_SESSION['_captcha_admin_login']) && isset($_POST['captcha']) && $_POST['captcha'] == $_SESSION['_captcha_admin_login']) {
                $_SESSION['_admin_user'] = [];
                if (isset($_SESSION['_admin_user'])) {
                    sfredirect('/admin');
                } else {
                    sfpush_admin_tmp_message('未登录');
                }
            } else {
                sfpush_admin_tmp_message('效验码不正确！');
            }
        }
        $bind = ['title' => '登录'];
        return view('admin/login', $bind);
    }

    public function captcha()
    {
        @session_start();
        $rand = sfrand_string(4, '0123456789');
        $_SESSION['_captcha_admin_login'] = $rand;
        $lib = new \Lib_Image();
        $lib->drawCode($rand);
    }
}
