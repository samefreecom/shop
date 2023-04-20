<?php
namespace app;

use think\Model;
use think\Db;

class BaseModel extends Model
{
    public $lastMessage;
    public $lastError;
    public $lastErrorCode = -1;
    public $errors = array();
    static $finalError;
    protected $lastId;

    /**
     * 类实例化（单例模式）
     */
    public static function instance()
    {
        $args = func_get_args();
        $classFullName = get_called_class();
        $lastKey = sfmd5_short(json_encode($args));
        if (!isset($GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName])) {
            if (class_exists($classFullName)) {
                if (!empty($args)) {
                    $instance = $GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName . $lastKey] = new static($args[0]);
                } else {
                    $instance = $GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName . $lastKey] = new static();
                }
                return $instance;
            }
        }
        return $GLOBALS['instances'][GLOBAL_INSTANCE_KEY][$classFullName . $lastKey];
    }

    public function hasError()
    {
        return !empty($this->lastError);
    }

    public function getError()
    {
        return $this->lastError;
    }

    public function getErrorCode()
    {
        return $this->lastErrorCode;
    }

    public function setError($content)
    {
        $args = func_get_args();
        $this->lastError = $content;
        if (count($args) > 1) {
            $this->lastError = call_user_func_array('sprintf', $args);
        }
        return false;
    }

    public function setErrorCode($code)
    {
        $this->lastErrorCode = $code;
        return $this;
    }

    /**
     * @param Exception $e
     * @return bool
     */
    public function setException($e)
    {
        return $this->setErrorCode($e->getCode())->setError($e->getMessage());
    }

    public function pushError($content, $code = -1, $data = null)
    {
        if ($code === null) {
            $code = -1;
        }
        $this->errors[] = array('content' => $content, 'code' => $code, $data);
    }

    public function getErrors()
    {
        if (empty($this->errors) && !empty($this->lastError)) {
            return array(array('content' => $this->lastError, 'code' => $this->lastErrorCode));
        }
        return $this->errors;
    }

    public function resetErrors()
    {
        $this->errors = array();
        return $this;
    }

    public function cloneError($object)
    {
        $this->lastError = $object->lastError;
        $this->lastErrorCode = $object->lastErrorCode;
        $this->errors = $object->errors;
        return false;
    }

    public function setErrorNull($content = '未知错误')
    {
        return $this->setErrorCode(100001)->setError($content);
    }
    
    public function begin()
    {
        Db::startTrans();
    }
    
    public function rollback()
    {
        Db::rollback();
    }

    public function eachLocal()
    {
        $contents = array();
        $dir = ROOT_DIR . DS . 'public' . DS . 'asset' . DS . 'internal' . DS . 'data' . DS . 'local' . DS . get_class($this);
        sfconsole('dir');
        sfconsole($dir);
        $list = \Lib_IoUtils::instance()->scanDir($dir);
        sfconsole('each list');
        sfconsole($list);
        foreach ($list as $value) {
            list($name, $ext) = explode('.', $value);
            $contents[$name] = $this->getLocal($name);
        }
        return $contents;
    }

    public function setLocal($key, $data)
    {
        $io = \Lib_IoUtils::instance();
        $file = ROOT_DIR . DS . 'public' . DS . 'asset' . DS . 'internal' . DS . 'data' . DS . 'local' . DS . get_class($this) . DS . $key . '.log';
        $io->createDir(dirname($file));
        $io->writeFile($file, serialize($data));
        return $this;
    }

    public function getLocal($key)
    {
        $io = \Lib_IoUtils::instance();
        $file = ROOT_DIR . DS . 'public' . DS . 'asset' . DS . 'internal' . DS . 'data' . DS . 'local' . DS . get_class($this) . DS . $key . '.log';
        if (is_file($file)) {
            $data = $io->readFile($file);
            if (!empty($data)) {
                return unserialize($data);
            }
        }
        return null;
    }

    public function deleteLocal($key)
    {
        $file = ROOT_DIR . DS . 'public' . DS . 'asset' . DS . 'internal' . DS . 'data' . DS . 'local' . DS . get_class($this) . DS . $key . '.log';
        @unlink($file);
        return $this;
    }

    public function setLastId($id)
    {
        $this->lastId = $id;
    }
    public function getLastId()
    {
        return $this->lastId;
    }
}