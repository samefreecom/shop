<?php
    namespace app\base\model;

    use app\BaseModel;
    use think\Db;
    class Summary extends BaseModel
    {
        public function getTodayIndent()
        {
            $data = Db::table(sfp('indent'))->where('to_days(created_at) = to_days(now())')->field(['count(1)' => 'total'])->find();
            return $data['total'];
        }

        public function getTodayGroup()
        {
            $data = Db::table(sfp('group'))->where('to_days(created_at) = to_days(now())')->field(['count(1)' => 'total'])->find();
            return $data['total'];
        }

        public function getTodayAccount()
        {
            $data = Db::table(sfp('account'))->where('to_days(created_at) = to_days(now())')->field(['count(1)' => 'total'])->find();
            return $data['total'];
        }
    }