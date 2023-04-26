<?php
namespace app\member\model;

use app\BaseModel;
use app\wx\model\Base;

class Session extends BaseModel
{
    public function getId() {
        $mWx = new Base();
        return $mWx->getOpenID();
    }
    
    public function getName() {
        @session_start();
        return isset($_SESSION['member_name']) ? $_SESSION['member_name'] : '';
    }
}