<?php
    namespace app\base\model;

    use app\BaseModel;
    use think\Db;
    class Setting extends BaseModel
    {
        private $configList = array();
        public function __construct($name = null)
        {
            parent::__construct($name);
            $configList =  Db::table(sfp('setting'))->select();
            foreach ($configList as $config) {
                $this->configList[$config['code']] = $config;
            }
        }

        public function __get($name)
        {
            if (isset($this->configList[$name]))
                return $this->configList[$name];
            return null;
        }

        public function getValue($name)
        {
            if (isset($this->configList[$name]))
                return $this->configList[$name]['value'];
            return null;
        }

        public static function instance()
        {
            $classFullName = get_called_class();
            if (!isset($GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName])) {
                if (class_exists($classFullName)) {
                    $instance = $GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName] = new static();
                    return $instance;
                }
            }
            return $GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName];
        }

        public function findGroupList()
        {
            $list = Db::table(sfp('setting'))->group('group')->field(['group' => 'code'])->select();
            return $list;
        }

        public function getGroupNameByCode($code)
        {
            switch ($code) {
                case 'default':
                    return '默认设置';
                    break;
                default:
                    return $code;
            }
        }

        public function getListByGroup($name)
        {
            $list = Db::table(sfp('setting'))->where('group', 'eq', $name)->select();
            return $list;
        }

        public function modifySetting($param)
        {
            if (sfis_valid($param, array('id' => 'array'))) {
                try {
                    $this->begin();
                    foreach ($param['id'] as $id => $value) {
                        $result = Db::table(sfp('setting'))->where('id', 'eq', $id)->update(array('value' => $value, 'updated_at' => date('Y-m-d H:i:s')));
                        if (!$result) {
                            throw new \Exception('修改配置项（' . $id . '）时失败！');
                        }
                    }
                    $this->commit();
                    return true;
                } catch (\Exception $e) {
                    $this->rollBack();
                    return $this->setException($e);
                }
            } else {
                return $this->setErrorCode(100500)->setError('找不到修改的配置数据或格式异常！');
            }
        }
    }