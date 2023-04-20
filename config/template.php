<?php
$view_path = './template/home/';
//判断是否是后台路径，如果是则用admin模块模板
foreach ($_REQUEST as $key => $value) {
    if (is_numeric(strpos($key, '/admin/')) || is_numeric(strpos($key, '\admin\\'))) {
        define('FLAG_ADMIN', true);
        //模板自定义事件   ::BOF
        /*
        sfregister_event('mold_list', function () {
            return array('article' => '文章类目', 'nav' => '导航类目', 'ad' => '广告类目');
        });
        sfregister_event('url_list', function () {
            return array(
                'name' => '文章'
            , 'url' => sfurl('/cms/admin/url')
            );
        });
        */
        //模板自定义事件   ::EOF
        break;
    }
}
//记录跳转  ::BOF
$request = \think\Container::get('request');
$curUrl = $request->url();
@session_start();
$_SESSION['cur_url'] = isset($_SESSION['cur_url']) ? $_SESSION['cur_url'] : $curUrl;
if (!isset($_REQUEST['ajax']) && !FLAG_AJAX) {
    if (isset($_SESSION['track'])) {
        $track = $_SESSION['track'];
    } else {
        $track = array('history' => array('front' => array(), 'admin' => array()));
    }
    if (!is_array($track['history'])) {
        $track['history'] = array('front' => array(), 'admin' => array());
    } elseif (!isset($track['history']['front']) || !is_array($track['history']['front'])) {
        $track['history']['front'] = array();
    } elseif (!isset($track['history']['admin']) || !is_array($track['history']['admin'])) {
        $track['history']['admin'] = array();
    }

    $curKey = !defined('FLAG_ADMIN') ? 'front' : 'admin';
    $len = count($track['history'][$curKey]);
    if ($len > 0 && $curUrl == @$track['history'][$curKey][$len - 1]) {
        //防止刷新重复
    } else {
        if ($len >= 3) {
            if ($curUrl == @$track['history'][$curKey][1]) {
                $track['history'][$curKey][1] = @$track['history'][$curKey][0];
            }
            $track['history'][$curKey] = array(
                0 => @$track['history'][$curKey][1],
                1 => @$track['history'][$curKey][2],
                2 => $_SESSION['cur_url']
            );
        } else {
            array_push($track['history'][$curKey], $_SESSION['cur_url']);
        }
        $_SESSION['cur_url'] = $curUrl;
    }
    $_SESSION['track'] = $track;
}
//记录跳转  ::EOF
function is_mobile(){
    // 如果有Http_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia',
            'sony',
            'eriCSSon',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])){
        // 如果只支持wml并且不支持HTML那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
        }
    }
    return false;
}
if (is_mobile() && is_dir(ROOT . '/template/mobile')) {
    $view_path = './template/mobile/';
}
return [
    // 模板引擎类型 支持 php think 支持扩展
    'type'         => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'    => 1,
    // 模板路径
    'view_path'    => $view_path,
    // 模板后缀
    'view_suffix'  => 'phtml',
    // 模板文件名分隔符
    'view_depr'    => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'    => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'      => '}',
    // 标签库标签开始标记
    'taglib_begin' => '{',
    // 标签库标签结束标记
    'taglib_end'   => '}',
    'tpl_replace_string' => [
        '__DOMAIN__' => ROOT_DIR
        , '__STATIC__' => ROOT_DIR . '/public/static'
    ]
];
