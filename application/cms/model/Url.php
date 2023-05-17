<?php
    namespace app\cms\model;

    use app\BaseModel;
    use think\Db;
    class Url extends BaseModel
    {
        public function saveUrl($type, $typeId, $value)
        {
            $url = Db::table(sfp('url'))->where('type', 'eq', $type)->where('type_id', 'eq', $typeId)->find();
            if (empty($url)) {
                if (!empty($value)) {
                    try {
                        $bind = array(
                            'type' => $type
                            , 'type_id' => $typeId
                            , 'value' => $value
                            , 'created_at' => date('Y-m-d H:i:s')
                            , 'updated_at' => date('Y-m-d H:i:s')
                        );
                        return Db::table(sfp('url'))->insert($bind);
                    } catch (\Exception $e) {
                        return $this->setErrorCode(100500)->setError('地址已存在，请更换其他地址！');
                    }
                } else {
                    return true;
                }
            } else {
                if ($value == $url['value']) {
                    return true;
                }
                if (empty($value)) {
                    Db::table(sfp('url'))->where('id', 'eq', $url['id'])->delete();
                    return true;
                }
                try {
                    $bind = array(
                        'value' => $value
                        , 'updated_at' => date('Y-m-d H:i:s')
                    );
                    return Db::table(sfp('url'))->where('id', 'eq', $url['id'])->update($bind);
                } catch (\Exception $e) {
                    return $this->setErrorCode(100500)->setError('地址已存在，请更换其他地址！');
                }
            }
        }

        public function getUrlValue($type, $typeId)
        {
            $url = Db::table(sfp('url'))->where('type', 'eq', $type)->where('type_id', 'eq', $typeId)->value('value');
            if (!empty($url)) {
                return $url;
            }
            return '';
        }

        public function formatUrl(&$list)
        {
            foreach ($list as $key => $value) {
                if (empty($value['url'])) {
                    $list[$key]['url'] = '/article/id/' . $value['id'];
                }
            }
        }
    }