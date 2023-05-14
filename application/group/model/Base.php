<?php
namespace app\group\model;

use app\base\model\Session;
use app\BaseModel;
use think\Db;
class Base extends BaseModel
{
    public function findList($param = array())
    {
        $session = new Session();
        $lon = $session->getLon();
        $lat = $session->getLat();
        $base = Db::table(sfp('group t'));
        if (!empty($param['keyword'])) {
            $autoNo = sprintf('%02d', $param['keyword']);
            $base->where('title like "%' . $param['keyword'] . '%" or auto_no = "' . $autoNo . '"');
        }
        $base->where('expired_at', '>=', date('Y-m-d'));
        $base->where('lon > ' . $lon . ' - 0.01 or lon <= ' . $lon . ' + 0.01');
        $base->where('lat > ' . $lat . ' - 0.01 or lat <= ' . $lat . ' + 0.01');
        $base->order(Db::raw('abs(lon - ' . $lon . ') + abs(lat - ' . $lat . ')'));
        $base->limit($param['limit'] * ($param['page'] - 1), $param['limit']);
        $list = $base->select();
        $groupIdList = [];
        $idMap = [];
        foreach ($list as $key => $value) {
            $list[$key]['diff'] = sfdiff_distance($lon, $lat, $value['lon'], $value['lat']);
            $groupIdList[] = $value['id'];
            $idMap[$value['id']] = $key;
        }
        $foodList = Db::table(sfp('indent_food t'))
            ->join(sfp('indent i'), 't.indent_id = i.id')
            ->leftJoin(sfp('food f'), 't.food_id = f.id')
            ->leftJoin(sfp('account a'), 'i.account_id = a.id')
            ->field(['t.*', 'f.name', 'i.group_id', 'a.name' => 'account_name'])
            ->where('i.group_id', 'in', $groupIdList)
            ->where('i.status', 'neq', 'E')
            ->order('a.id desc')->select();
        foreach ($foodList as $value) {
            $idKey = $idMap[$value['group_id']];
            if (!isset($list[$idKey]['foods'])) {
                $list[$idKey]['foods'] = [];
            }
            $list[$idKey]['foods'][] = $value;
        }
        return $list;
    }
    
    public function add($param)
    {
        sftrim($param);
        $exists = Db::table(sfp('group'))->where('telephone', 'eq', $param['telephone'])->where('expired_at', 'eq', date('Y-m-d'))->find();
        if (!empty($exists)) {
            return $this->setError('一个联系方式只能创建一个团体！');
        }
        $keyMap = ['title' => '标题', 'name' => '称呼', 'telephone' => '联系方式', 'address' => '配送地址', 'note' => '备注详情', 'lon' => '经度', 'lat' => '纬度'];
        if (($key = sfis_valid($param, array('title', 'name', 'telephone', 'address', 'note', 'lon' => 'numeric', 'lat' => 'numeric'))) !== true) {
            return $this->setErrorCode(404)->setError('%s 不能为空或不符合！', $keyMap[$key]);
        }
        $max = Db::table(sfp('group'))->where('expired_at', '>=', date('Y-m-d'))->count('1');
        $autoNo = sprintf('%02d', $max + 1);
        $bind = [
            'group_no' => date('ymd') . $autoNo
            , 'auto_no' => $autoNo
            , 'title' => $param['title']
            , 'name' => $param['name']
            , 'telephone' => $param['telephone']
            , 'address' => $param['address']
            , 'note' => $param['note']
            , 'pwd' => $param['pwd']
            , 'lon' => $param['lon']
            , 'lat' => $param['lat']
            , 'created_at' => date('Y-m-d H:i:s')
            , 'created_id' => Session::instance()->getId()
            , 'expired_at' => date('Y-m-d')
        ];
        if (Db::table(sfp('group'))->insert($bind)) {
            return true;
        } else {
            return $this->setError('插入数据库失败，请联系技术客服！');
        }
    }
}