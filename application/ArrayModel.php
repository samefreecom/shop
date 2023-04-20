<?php
    namespace app;
    use think\Db;

    class ArrayModel extends BaseModel implements \ArrayAccess
    {
        public $arrayList = [];
        public function __construct($tableName)
        {
            parent::__construct([]);
            $list = Db::table(sfp($tableName))->select();
            foreach ($list as $value) {
                $this->arrayList[$value['code']] = $value;
            }
        }

        public function offsetGet($code)
        {
            return $this->arrayList[$code];
        }

        public function getFieldName($code)
        {
            if (isset($this->arrayList[$code]))
                return $this->arrayList[$code]['name'];
            else
                return '';
        }
    }