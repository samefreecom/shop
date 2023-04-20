<?php
    namespace app\wap\controller;

    class Index extends Base
    {
        public function index()
        {
            $bind = [];
            return view('index', $bind);
        }
    }
