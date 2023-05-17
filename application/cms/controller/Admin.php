<?php
namespace app\cms\controller;
use app\cms\model\Ad;
use app\cms\model\Clist;
use app\cms\model\Code;
use app\cms\model\Mold;
use app\cms\model\Nav;
use app\cms\model\Url;
use think\facade\Request;

class Admin
{
    public function addNavGroup()
    {
        if (Request::isPost()) {
            $obj = new Mold();
            if ($obj->addMoldByName($_POST['name'])) {
                sfresponse(1, '添加成功！', array('id' => $obj->getLastInsID(), 'callback' => 'callback_name'));
            } else {
                sfpush_admin_tmp_message($obj->getError(), '', null, array('callback' => 'callback_name'));
            }
        }
        return view('admin/nav/add_group');
    }
    
    public function editNavGroup()
    {
        $obj = new Mold();
        $param = Request::param();
        if (Request::isPost()) {
            if ($obj->modifyNameById($param['id'], $_POST['name'])) {
                sfresponse(1, '更新成功！', array('id' => $param['id'], 'callback' => 'callback_name'));
            } else {
                sfpush_admin_tmp_message($obj->getError(), '', null, array('callback' => 'callback_name'));
            }
        }
        $param['data'] = $obj->getMoldById($param['id']);
        if (empty($param['data'])) {
            sfpush_admin_tmp_message($obj->getError(), '', null, array('callback' => 'callback_name'));
        }
        return view('admin/nav/edit_group', $param);
    }
    
    public function nav()
    {
        $param = Request::param();
        $bind = array();
        $mold = new Mold();
        $moldList = $mold->findListByType('nav');
        $bind['moldList'] = $moldList;
        $data = null;
        if (!empty($param['id'])) {
            $data = $mold->findListByMoldId($param['id']);
        } else {
            $data = $mold->findNavListByDefMold();
        }
        if (!empty($data)) {
            $bind['mold'] = $data['mold'];
            $bind['list'] = $data['list'];
        }
        return view('admin/nav/index', $bind);
    }

    public function addNav()
    {
        $param = Request::param();
        if (Request::isPost()) {
            $obj = new Nav();
            if ($obj->addNav($param)) {
                sfresponse(1, '添加成功！', array('id' => $obj->getData('id'), 'callback' => 'callback_name'));
            } else {
                sfpush_admin_tmp_message($obj->getError(), '', null, array('callback' => 'callback_name'));
            }
        }
        return view('admin/nav/add', $param);
    }

    public function editNav()
    {
        $param = Request::param();
        $obj = new Nav();
        if (Request::isPost()) {
            if ($obj->modifyNav($param)) {
                sfresponse(1, '更新成功！', array('callback' => 'callback_close'));
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $param['data'] = $obj->getNav($param);
        if (empty($param['data'])) {
            sfpush_admin_tmp_message($obj->getError(), '', null, array('callback' => 'callback_name'));
        }
        return view('admin/nav/edit', $param);
    }


    public function delNav()
    {
        if (Request::isPost() && !empty($_POST['id'])) {
            $obj = new Nav();
            sfresponse($obj->deleteNavById($_POST['id']), $obj->getError());
        } else {
            sfresponse(0, '缺少必要参数！');
        }
    }
    
    public function delNavGroup()
    {
        if (Request::isPost() && !empty($_POST['id'])) {
            $obj = new Mold();
            sfresponse($obj->deleteMoldById($_POST['id']), $obj->getError());
        } else {
            sfresponse(0, '缺少必要参数！');
        }
    }

    public function relateNav()
    {
        $obj = new Nav();
        sfresponse($obj->relateNav($_POST), $obj->getError());
    }
    
    public function mold()
    {
        $type = sfret('type', 'article');
        $obj = new Mold();
        $bind = array();
        $bind['type'] = $type;
        $bind['moldList'] = sftrigger_event('mold_list');
        $bind['moldList'] = $bind['moldList'] ? $bind['moldList'] : [];
        $bind['tree'] = $obj->getTreeByType($type);
        return view('admin/mold/index', $bind);
    }

    public function addMold()
    {
        $obj = new Mold();
        if (Request::isPost()) {
            if ($obj->addMold($_POST)) {
                sfresponse(1, '添加成功！', array('callback' => 'callback_name'));
            } else {
                sfpush_admin_tmp_message($obj->getError(), '', null, array('callback' => 'callback_name'));
            }
        }
        $type = sfret('type', 'article');
        $bind = array();
        $bind['tree'] = $obj->getTreeByType($type);
        return view('admin/mold/add', $bind);
    }

    public function modifyMoldName()
    {
        $obj = new Mold();
        if (Request::isPost()) {
            if (!empty($_POST['id'])) {
                sfresponse($obj->modifyNameById($_POST['id'], $_POST['name']),$obj->getError());
            }
        }
        sfresponse(0, '缺少必要参数！');
    }

    public function delMold()
    {
        $obj = new Mold();
        if (Request::isPost()) {
            if (!empty($_POST['id'])) {
                sfresponse($obj->deleteMoldById($_POST['id']),$obj->getError());
            }
        }
        sfresponse(0, '缺少必要参数！');
    }

    public function modifyMoldSort()
    {
        $obj = new Mold();
        if (Request::isPost()) {
            if (!empty($_POST['id'])) {
                sfresponse($obj->modifySort($_POST['id']),$obj->getError());
            }
        }
        sfresponse(0, '缺少必要参数！');
    }

    public function modifyMoldParent()
    {
        $obj = new Mold();
        if (!empty($_POST['id'])) {
            sfresponse($obj->modifyParentById($_POST['id'], $_POST['parent_id']), $obj->getError());
        } else {
            sfresponse(0, '缺少必要参数！');
        }
    }

    public function index()
    {
        $obj = new Mold();
        $type = sfret('type', 'article');
        $bind = array();
        $bind['type'] = $type;
        $bind['tree'] = $obj->getTreeByType($type);
        return view('admin/list/index', $bind);
    }

    public function getList()
    {
        $obj = new Clist();
        $param = Request::param();
        $param['type'] = sfret('type', 'article');
        $ret = array(
            'code' => 0
            , 'msg' => ''
            , 'count' => $obj->findListTotal($param)
        );
        if (!empty($param['limit'])) {
            $ret['data'] = $obj->findList($param);
        }
        sfquit(json_encode($ret));
    }
    
    public function addList()
    {
        $objMold = new Mold();
        $obj = new Clist();
        $type = sfret('type', 'article');
        if (Request::isPost()) {
            if ($obj->addList($_POST)) {
                if (isset($_POST['action']) && $_POST['action'] == 'adds') {
                    sfresponse(1, '添加成功');
                } else {
                    sfquit('<script>parent.sfclosetab("list_add", "添加成功！")</script>');
                }
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $bind = array();
        $bind['type'] = $type;
        $bind['tree'] = $objMold->getTreeByType($type);
        return view('admin/list/add', $bind);
    }

    public function editList()
    {
        $param = Request::param();
        if (empty($param['id'])) {
            sfresponse(0, '缺少必要参数！');
        }
        $obj = new Clist();
        if (!empty($_POST)) {
            if ($obj->modifyList($_POST)) {
                sfquit('<script>parent.sfclosetab("list_edit_' . $param['id'] . '", "更新成功！")</script>');
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $data = $obj->getById($param['id']);
        if (empty($data)) {
            sfresponse(0, '找不到数据！');
        }
        $objMold = new Mold();
        $type = sfret('type', 'article');
        $bind = array();
        $bind['data'] = $data;
        $bind['tree'] = $objMold->getTreeByType($type);
        return view('admin/list/edit', $bind);
    }

    public function delList()
    {
        if (!empty($_POST['id'])) {
            $obj = new Clist();
            sfresponse($obj->deleteListById($_POST['id']), $obj->getError());
        } else {
            sfresponse(0, '缺少必要参数！');
        }
    }
    
    public function ad()
    {
        $param = Request::param();
        $objMold = new Mold();
        $obj = new Ad();
        $type = 'ad';
        $bind = array();
        $bind['tree'] = $objMold->getTreeByType($type);
        $bind['list'] = $obj->findList($param);
        $bind['total'] = $obj->findListTotal($param);
        return view('admin/ad/index', $bind);
    }

    public function addAd()
    {
        $objMold = new Mold();
        if (Request::isPost()) {
            $obj = new Ad();
            if ($obj->addAd($_POST)) {
                sfpush_admin_tmp_message('添加成功！');
                sfredirect();
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $type = 'ad';
        $bind = array();
        $bind['tree'] = $objMold->getTreeByType($type);
        return view('admin/ad/add', $bind);
    }

    public function editAd()
    {
        $param = Request::param();
        $objMold = new Mold();
        $obj = new Ad();
        if (Request::isPost()) {
            if ($obj->modifyAd($_POST)) {
                sfquit('<script>parent.sfclosetab("ad_edit_'. $param['id'] . '", "修改成功！")</script>');
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $type = 'ad';
        $bind = array();
        $bind['tree'] = $objMold->getTreeByType($type);
        $bind['data'] = $obj->getAdById($param['id']);
        return view('admin/ad/edit', $bind);
    }

    public function delAd()
    {
        if (!empty($_POST['id'])) {
            $obj = new Ad();
            sfresponse($obj->deleteAdById($_POST['id']), $obj->getError());
        } else {
            sfresponse(0, '缺少必要参数！');
        }
    }
    
    public function upload()
    {
        if (!empty($_FILES['file'])) {
            $ret = \Lib_Image::instance()->upload($_FILES['file'], sfret('type'), true, sfret('title'));
            if (is_string($ret)) {
                sfresponse(1, '', array('path' => $ret, 'url' => sfurl($ret)));
            } else {
                sfresponse(0, \Lib_Image::instance()->getError());
            }
        } else {
            sfresponse(0, '未找到上传文件！');
        }
    }

    public function url()
    {
        $param = Request::param();
        $type = sfret('type', 'article');
        $param['type'] = $type;
        $data = array();
        if (!isset($param['keyword'])) {
            $objMold = new Mold();
            $data = $objMold->getTreeByType($type);
        } else {
            $objList = new Clist();
            $objUrl = new Url();
            $param['title'] = $param['keyword'];
            $param['limit'] = 10;
            $data = $objList->findList($param);
            $objUrl->formatUrl($data);
        }
        sfresponse(1, '', $data);
    }
    
    public function code()
    {
        $param = Request::param();
        $obj = new Code();
        $bind = array();
        $bind['list'] = $obj->findList($param);
        $bind['total'] = $obj->findListTotal($param);
        return view('admin/code/index', $bind);
    }

    public function addCode()
    {
        if (Request::isPost()) {
            $obj = new Code();
            if ($obj->addCode($_POST)) {
                sfpush_admin_tmp_message('添加成功！');
                sfredirect();
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }

        }
        $bind = array();
        return view('admin/code/add', $bind);
    }

    public function editCode()
    {
        $param = Request::param();
        $obj = new Code();
        if (Request::isPost()) {
            if ($obj->modifyCode($_POST)) {
                sfpush_admin_tmp_message('修改成功！');
            } else {
                sfpush_admin_tmp_message($obj->getError());
            }
        }
        $bind = array();
        $bind['data'] = $obj->getCodeById($param['id']);
        return view('admin/code/edit', $bind);
    }
}
