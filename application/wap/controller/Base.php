<?php
    namespace app\wap\controller;
    use think\facade\View;
    class Base
    {
        public function __construct()
        {
            View::config('view_path', './template/wap/');
        }
    }