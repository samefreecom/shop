<?php
    namespace app\base\controller;

    require_once ROOT . '/application/lib/Mq.php';

    class Consume
    {
        public function index()
        {
            $mq = \Lib_Mq::instance();
            $mq->initTask();
            $bind = [];
            $bind['event'] = $mq->execTask('event');
            $bind['path'] = $mq->execTask('path');
            $bind['export'] = $mq->execTask('export', 1);
            return view('index', $bind);
        }

        public function test()
        {
        }
    }