<?php
namespace app\admin\model;

use app\BaseModel;
use think\Db;

class User extends BaseModel
{
    public function login($account, $oldPassword)
    {
        $base = Db::table(sfp('user u'));
        $base->where('u.account', 'eq', $account);
        $base->limit(1);
        $data = $base->find();
        if (!empty($data)) {
            if (!sfcheck($oldPassword, $data['password'])) {
                return $this->setErrorCode(403)->setError('用户或密码不正确！');
            }
            return true;
        } else {
            return $this->setErrorCode(404)->setError('找不到该用户名！');
        }
    }

    public function register($param)
    {
        $bind = array(
            'account' => $param['account']
            , 'password' => sfencode($param['passwords'])
            , 'name' => $param['account']
            , 'telephone' => $param['account']
            , 'created_at' => date('Y-m-d H:i:s')
            , 'updated_at' => date('Y-m-d H:i:s')
        );
        $result = Db::table(sfp('user'))->insert($bind);
        if ($result) {
            return true;
        }
        return false;
    }
}