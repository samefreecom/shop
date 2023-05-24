<?php
    namespace app\base\controller;

    class Image
    {
        public function barcode()
        {
            $image = new \Lib_Image();
            $image->barcode($_GET['no'], sfret('size', '1-40'));
            sfquit();
        }
        
        public function qrcode()
        {
            $image = new \Lib_Image();
            $image->qrcode($_GET['no'], 'png', null, sfret('size', '3'));
            sfquit();
        }
    }