<?php
namespace app\base\model;

use app\BaseModel;
use think\Db;
class Common extends BaseModel
{
    public function getRandNetName()
    {
        return Db::table(sfp('net_name'))->orderRand()->limit(1)->value('name');
    }
}