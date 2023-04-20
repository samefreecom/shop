<?php
    class Lib_Base
    {
        /**
         * 所有子类首次实例化预留为全局对象
         */
        function __construct()
        {
            $className = get_class($this);
            if (!isset($GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$className])) {
                $GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$className] = $this;
            }
        }

        /**
         * 类实例化（单例模式）
         */
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
    }