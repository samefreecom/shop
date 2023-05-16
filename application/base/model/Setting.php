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
        $base =  Db::table(sfp('setting t'));
        if (defined('DOMAIN_CODE')) {
            $base->leftJoin(sfp('site s'), 't.site_id = s.id');
            $base->where('s.code', 'eq', DOMAIN_CODE);
            $base->field('t.*');
            $configList = $base->select();
            if (empty($configList)) {
                $base =  Db::table(sfp('setting t'));
                $base->where('site_id', 'eq', 0);
                $configList = $base->select();
            }
        } else {
            $base->where('site_id', 'eq', 0);
            $configList = $base->select();
        }
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

    public function getList($param)
    {
        $base = Db::table(sfp('setting t'))->order('t.code desc');
        if (!empty($param['group'])) {
            $base->where('group', 'eq', $param['group']);
        }
        if (!empty($param['site'])) {
            $base->leftJoin(sfp('site s'), 't.site_id = s.id');
            $base->where('s.code', 'eq', $param['site']);
            $base->field(['t.*', 's.code' => 'site_code']);
            $list = $base->select();
            $siteId = 0;
            if (!empty($list)) {
                $siteId = $list[0]['site_id'];
            } else {
                $site = Db::table(sfp('site'))->where('code', 'eq', $param['site'])->limit(1)->find();
                if (!empty($site)) {
                    $siteId = $site['id'];
                }
            }
            if (empty($siteId)) {
                return [];
            }
            $listFmt = [];
            foreach ($list as $value) {
                $listFmt[$value['code']] = $value;
            }
            $baseTmp = Db::table(sfp('setting t'))->where('site_id', 'eq', 0);
            if (!empty($param['group'])) {
                $baseTmp->where('group', 'eq', $param['group']);
            }
            $listTmp = $baseTmp->select();
            foreach ($listTmp as $value) {
                if (!isset($listFmt[$value['code']])) {
                    $value['value'] = '';
                    $value['id'] = $value['code'];
                    $value['site_id'] = $siteId;
                    $listFmt[$value['code']] = $value;
                }
            }
            $list = array_merge($listFmt);
        } else {
            $base->where('site_id', 'eq', 0);
            $list = $base->select();
        }
        return $list;
    }

    public function saveSetting($param)
    {
        if (sfis_valid($param, array('id' => 'array'))) {
            try {
                $this->begin();
                $baseTmp = Db::table(sfp('setting t'))->where('site_id', 'eq', 0);
                if (!empty($param['group'])) {
                    $baseTmp->where('group', 'eq', $param['group']);
                }
                $listFmt = [];
                $list = $baseTmp->select();
                foreach ($list as $value) {
                    $listFmt[$value['code']] = $value;
                }
                foreach ($param['id'] as $id => $value) {
                    list($siteId, $id) = explode('__', $id);
                    if (is_numeric($id)) {
                        $result = Db::table(sfp('setting'))->where('id', 'eq', $id)->update(array('value' => $value, 'updated_at' => date('Y-m-d H:i:s')));
                        if (!$result) {
                            throw new \Exception('修改配置项（' . $id . '）时失败！');
                        }
                    } else {
                        $bind = array(
                            'site_id' => $siteId
                        , 'group' => $param['group']
                        , 'code' => $id
                        , 'value' => $value
                        , 'format' => 'text'
                        , 'created_at' => date('Y-m-d H:i:s')
                        , 'updated_at' => date('Y-m-d H:i:s')
                        );
                        if (isset($listFmt[$bind['code']])) {
                            $dataFmt = $listFmt[$bind['code']];
                            $bind['name'] = $dataFmt['name'];
                            $bind['format'] = $dataFmt['format'];
                            $bind['format_data'] = $dataFmt['format_data'];
                            $bind['note'] = $dataFmt['note'];
                        }
                        Db::table(sfp('setting'))->insert($bind);
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