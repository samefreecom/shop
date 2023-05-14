<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

define('ENDE_KEY', '_sf20180106808');
define('GLOBAL_INSTANCE_KEY', '__');
define('FLAG_BASEDIR', false);
define('DS', DIRECTORY_SEPARATOR);
define('FLAG_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? ($_SERVER['HTTP_X_REQUESTED_WITH']== 'XMLHttpRequest') : false);
define('FLAG_WX', strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'));
define('ROOT', dirname(dirname(__FILE__)));
define('DOMAIN', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
define('BASEURL', 'http://' . DOMAIN);  //  e.g. localhost
if (!defined('BASEDIR')) {
    if (FLAG_BASEDIR == true) {
        $basedir = ltrim(dirname($_SERVER['SCRIPT_NAME']));
        $basedir = ($basedir === DS ? '' : $basedir);
        define('BASEDIR', $basedir);
    } else {
        define('BASEDIR', '');
    }
}
define('SITEURL_ROOT', BASEURL . BASEDIR);
define('SITEURL', SITEURL_ROOT . '/'); //  e.g. http://localhost/[dirname

//include 'lib/360_safe3.php';
include 'lib/Base.php';
include 'lib/Image.php';
include 'lib/IoUtils.php';
include 'lib/Mq.php';

// 应用公共文件
if (!function_exists('lcfirst')) {
    function lcfirst($str)
    {
        return strtolower(substr($str, 0, 1)) . substr($str, 1);
    }
}
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
/**
 * 读取etc配置
 * @param $path
 * @return array|bool
 */
function sfread_etc($path)
{
    return parse_ini_file(ROOT_DIR . '/etc/' . $path);
}

/**
 * 读取etc
 * @param $path 配置路径
 * @param $key  配置key值
 * @return mixed
 */
function sfread_etc_global($path, $key)
{
    if (empty($GLOBALS['_etc_' . $path])) {
        $GLOBALS['_etc_' . $path] = sfread_etc($path);
    }
    return $GLOBALS['_etc_' . $path][$key];
}

/**
 * 返回指定数据的值
 * @param $key  值的键名
 * @param string $return    为空时返回的值
 * @param null $datas   数组数据，默认是 $_REQUEST
 * @param null $max 最大值，可选
 * @return mixed|string 返回值
 */
function sfret($key, $return = '', $datas = null, $max = null)
{
    if ($datas == null) {
        $datas = $_REQUEST;
        $datas = array_merge($datas, Request::param());
    }
    if (isset($datas[$key]) && !empty($datas[$key])) {
        $return = $datas[$key];
    }
    if ($max != null) {
        if ($return > $max) {
            $return = $max;
        }
    }
    return $return;
}
$GLOBALS['headers'] = getallheaders();
if (!empty($etcDebug['console']) && empty($GLOBALS['_console_mode'])) {
    $browser = '';
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $browser = 'MSIE';
        } elseif (preg_match('/Firefox/i', $br)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $br)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $br)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera/i', $br)) {
            $browser = 'Opera';
        } else {
            $browser = 'Other';
        }
    }
    $consoleFile = ROOT_DIR . DS . 'class' . DS . 'Console' . DS . strtolower($browser) . '.php';
    if ($etcDebug['console'] == 1 && is_file($consoleFile)) {
        require $consoleFile;
    } else {
        $browser = '';
    }
    if (empty($browser) || !function_exists('sfconsole')) {
        function sfconsole()
        {
            list($usec, $sec)   =   explode(' ', microtime());
            $GLOBALS['console_logs'][] = array('d-' . ((float)$usec + (float)$sec), func_get_args());
            global $_log_i;
            $_log_i = isset($_log_i) ? $_log_i : 0;
            header('Log-A-' . sprintf('%03d', $_log_i++) . ':' . json_encode(func_get_args()));
        }
        function sfconsolew()
        {
            list($usec, $sec)   =   explode(' ', microtime());
            $GLOBALS['console_logs'][] = array('w-' . ((float)$usec + (float)$sec), func_get_args());
            global $_log_i;
            $_log_i = isset($_log_i) ? $_log_i : 0;
            header('Log-W-' . sprintf('%03d', $_log_i++) . ':' . json_encode(func_get_args()));
        }
        function sfconsolee()
        {
            list($usec, $sec)   =   explode(' ', microtime());
            $GLOBALS['console_logs'][] = array('e-' . ((float)$usec + (float)$sec), func_get_args());
            global $_log_i;
            $_log_i = isset($_log_i) ? $_log_i : 0;
            header('Log-E-' . sprintf('%03d', $_log_i++) . ':' . json_encode(func_get_args()));
        }
        function sfconsolel()
        {
            list($usec, $sec)   =   explode(' ', microtime());
            $GLOBALS['console_logs'][] = array('l-' . ((float)$usec + (float)$sec), func_get_args());
            global $_log_i;
            $args = func_get_args();
            $_log_i = isset($_log_i) ? $_log_i : 0;
            header('Log-A-' . sprintf('%03d', $_log_i++) . ':' . $args[0]);
            array_splice($args, 0, 1);
            header('Log-A-' . sprintf('%03d', $_log_i) . ':' . json_encode($args));
        }
    }
} else {
    function sfconsole()
    {
        $GLOBALS['console_logs'][] = func_get_args();
        list($usec, $sec)   =   explode(' ', microtime());
        $GLOBALS['console_logs'][] = array('d-' . ((float)$usec + (float)$sec), func_get_args());
    }
    function sfconsolew()
    {
        list($usec, $sec)   =   explode(' ', microtime());
        $GLOBALS['console_logs'][] = array('w-' . ((float)$usec + (float)$sec), func_get_args());
    }
    function sfconsolee()
    {
        list($usec, $sec)   =   explode(' ', microtime());
        $GLOBALS['console_logs'][] = array('e-' . ((float)$usec + (float)$sec), func_get_args());
    }
    function sfconsolel()
    {
        list($usec, $sec)   =   explode(' ', microtime());
        $GLOBALS['console_logs'][] = array('l-' . ((float)$usec + (float)$sec), func_get_args());
    }
}
//读取全局配置    ::结束

function sfmodule_config($name)
{
    $confFile = MODULE_DIR . DS . $name . DS . 'config.xml';
    if (is_file($confFile)) {
        $file = $confFile;
    } else {
        //TODO
        // $d module have no config
        return;
    }
    $tmpData = sfdecode_xml_file_to_array($file);
    $exConfFile = EXMODULE_CUR_DIR . DS . $name . DS . 'config.xml';
    if (is_file($exConfFile)) {
        $addTmpData = sfdecode_xml_file_to_array($exConfFile);
        $tmpData = $tmpData + $addTmpData;
    }
    return $tmpData;
}

//默认异步请求（瞬间中断）
//open_url('/test')
//open_url('/test?a=b', array('c' => 'd'))
//open_url('http://www.baidu.com')
function sfopen_url($url, $param = array(), $method = 'get', $args = array('return' => false))
{
    if (!empty($param) && is_string($param)) {
        //针对json请求
        $ch = curl_init($url);
        $headers = array();
        if (!empty($args['headers'])) {
            foreach ($args['headers'] as $k => $v) {
                $headers[] = sprintf('%s:%s', $k, $v);
            }
        }
        if (!empty($args['type'])) {
            $headers[] = 'Content-Type:' . $args['type'];
        } else {
            $headers[] = 'Content-Type:application/json';
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        if (empty($output)) {
            $error = curl_error($ch);
            if (!empty($error)) {
                sfdebug($error, 'open_url_bad');
            }
        }
        return $output;
    }
    $contentType = "Content-type:application/x-www-form-urlencoded\r\n";
    $cfg = parse_url($url);
    if (!isset($cfg['host'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        $host = $cfg['host'];
    }
    if (isset($cfg['scheme'])) {
        $port = $cfg['scheme'] == 'https' ? 443 : 80;
    } else {
        $port = $_SERVER['SERVER_PORT'];
    }
    $path = empty($cfg['path']) ? '/' : $cfg['path'];
    $sessionId = session_id();
    if (!empty($sessionId)) {
        $cookie = "PHPSESSID=" . $sessionId;
    }
    $data = "";
    if (!empty($cfg['query'])) {
        $addParam = array();
        parse_str($cfg['query'], $addParam);
        $param = $param + $addParam;
    }
    if (!empty($param)) {
        if (is_array($param)) {
            $data = http_build_query($param);
        } else {
            $data = $param;
            $contentType = "Content-type:application/json\r\n";
        }
    }
    if (!empty($args['type'])) {
        $contentType = "Content-type:" . $args['type'];
    }
    $method = strtolower($method);
    if ($method == 'post') {
        $out = "POST ${path} HTTP/1.1\r\n";
        $out .= "Host: " . $host . "\r\n";
        $out .= $contentType;
        if (!empty($args['headers'])) {
            foreach ($args['headers'] as $key => $value) {
                $out .= sprintf("%s: %s\r\n", $key, $value);
            }
        }
        $out .= "Expect: 100-continue\r\n";
        $out .= "User-Agent: Mozilla/5.0(compatible;MSIE 9.0; Windows NT 6.1; Trident/5.0)\r\n";
        $out .= "Accept: text/html, application/xhtml+xml, */*\r\n";
        if (!empty($data)) {
            $out .= "Content-length:" . strlen($data) . "\r\n";
            $out .= "\r\n${data}";
        }
    } else {
        if (!empty($data)) {
            $path .= '?' . $data;
        }
        $out = "GET ${path} HTTP/1.1\r\n";
        $out .= "Host: " . $host . "\r\n";
        if (!empty($args['headers'])) {
            foreach ($args['headers'] as $key => $value) {
                $out .= sprintf("%s: %s\r\n", $key, $value);
            }
        }
    }
    $out .= "Connection: Close\r\n";
    if (!empty($cookie)) {
        $out .= "Cookie: " . $cookie . "\r\n\r\n";
    }
    $errno = 0;
    $errstr = "";
    $fp = fsockopen($host, $port, $errno, $errstr, !$args['return'] ? 1: (isset($args['timeout']) ? $args['timeout'] : 80));
    //@stream_set_blocking($fp, 0);
    @stream_set_timeout($fp, 10);
    fwrite($fp, $out);
    $response = '';
    if ($args['return']) {
        while($row = fread($fp, 4096)){
            $response .= $row;
        }
        $response = explode("\r\n\r\n", $response);
        foreach ($response as $value) {
            if (substr(strtolower($value), 0, 5) != 'http/') {
                $response = $value;
                break;
            }
        }
        if (is_numeric(strpos($response, "\r\n{"))) {
            $response = explode("\r\n{", $response);
            $response =  '{' . $response[1];
            $response = explode("}\r\n", $response);
            $response =  $response[0] . '}';
        }
    }
    if (!empty($errstr)) {
        if (!empty($errstr)) {
            sfdebug($errstr, 'open_url_bad');
        }
    }
    fclose($fp);
    return $response;
}

/**
 * 注册事件
 * @param $event    事件名称
 * @param Method|string $object   方法对象
 * @param null $key 事件唯一键名，默认则叠加不替换
 * @return int|null 事件唯一键名
 */
function sfregister_event($event, $object, $key = null)
{
    if ($key === null || is_numeric($key)) {
        for ($i = $key ? $key : 0; $i <= 999999999; $i++) {
            if (!isset($GLOBALS['events'][$event][$i])) {
                $GLOBALS['events'][$event][$i] = $object;
                return $i;
            }
        }
    } else {
        $GLOBALS['events'][$event][$key] = $object;
    }
    return $key;
}

/**
 * 触发事件
 * @param string $event 事件名称
 * @param int $retMode 返回模式（-1直接返回, 0不返回, 1累积结果返回）
 * @param string $scale 比例范围
 * @return array|mixed|null 事件处理后的结果
 */
function sftrigger_event($event, $retMode = 1, $scale = null)
{
    $params = func_get_args();
    array_splice($params, 0, 3);
    if (!empty($GLOBALS['events'][$event])) {
        $returns = array();
        $events = $GLOBALS['events'][$event];
        //部分处理逻辑
        if (!empty($scale) && is_numeric(strpos($scale, '-'))) {
            $total = count($events);
            $len = $total / 10;
            list($begin, $end) = explode('-', $scale);
            if ($end > $begin) {
                $qtyBegin = floor($begin * $len);
                $qtyEnd = floor(($end - $begin)  * $len);
                if ($qtyBegin < $total) {
                    $events = array_slice($events, $qtyBegin, $qtyEnd < 1 ? 1 : $qtyEnd);
                } else {
                    $events = array();
                }
            }
        }
        krsort($events);
        foreach ($events as $key => $value) {
            try {
                switch ($retMode) {
                    case -1:
                        if (is_object($value)) {
                            if ($value instanceof \Closure) {
                                $result = call_user_func_array($value , $params);
                            } else {
                                $result = call_user_func_array(array($value, 'execute') , $params);
                            }
                            if ($result !== null) {
                                return $result;
                            }
                        } elseif (is_string($value)) {
                            $result = include $value;
                            if ($result !== null) {
                                return $result;
                            }
                        }
                        break;
                    case 0:
                        if (is_object($value)) {
                            if ($value instanceof \Closure) {
                                call_user_func_array($value , $params);
                            } else {
                                call_user_func_array(array($value, 'execute') , $params);
                            }
                        } else {
                            include $value;
                        }
                        break;
                    case 1:
                        if (is_object($value)) {
                            if ($value instanceof \Closure) {
                                $returns[] = call_user_func_array($value , $params);
                            } else {
                                $returns[] = call_user_func_array(array($value, 'execute') , $params);
                            }
                        } else {
                            $returns[] = include $value;
                        }
                        break;
                }
            } catch (Exception $e) {
                if (!isset($GLOBALS['event_exceptions'])) {
                    $GLOBALS['event_exceptions'] = array($event => array());
                } else {
                    if (!isset($GLOBALS['event_exceptions'][$event])) {
                        $GLOBALS['event_exceptions'][$event] = array();
                    }
                }
                $GLOBALS['event_exceptions'][$event][] = $e;
            }
        }
        if ($retMode === 1) {
            return $returns;
        }
    }
    return null;
}

/**
 * 移除事件
 * @param string $event    事件名称
 * @param string $key  事件唯一键名
 */
function sfremove_event($event, $key)
{
    unset($GLOBALS['events'][$event][$key]);
}

/**
 * 清除所有事件
 * @param string $event    事件名称
 */
function sfclear_event($event)
{
    unset($GLOBALS['events'][$event]);
}

/**
 * 获取事件异常
 * @param string $event    事件名称
 * @param null $nullReturn  无异常内容时默认返回的异常对象
 * @return Exception    事件的第一个异常对象
 */
function sfget_event_exception($event, $nullReturn = null)
{
    if (empty($GLOBALS['event_exceptions'][$event])) {
        return $nullReturn;
    }
    return current($GLOBALS['event_exceptions'][$event]);
}

/**
 * 获取事件异常
 * @param string $event   事件名称
 * @return Exception[]    事件的第一个异常对象
 */
function sfget_event_exception_list($event)
{
    return $GLOBALS['event_exceptions'][$event];
}


/**
 * 获取IP地址
 * @return array|false|mixed|string
 */
function sfget_ip()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realip = getenv( "HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    $ip = empty($realip) ? '-' : $realip;
    if (is_numeric(strpos($ip, ','))) {
        $ips = explode(',', $ip);
        return current($ips);
    }
    return $ip;
}

/**
 * 将 xml 文件解析成数组并返回数组
 * @param  String $file 文件路径
 * @return array
 */
function sfdecode_xml_file_to_array($file)
{
    return sfparse_xml_to_array(file_get_contents($file));
}

/**
 * 将 xml 文件解析成数组并返回数组
 * @param  String $data   xml 格式字符串
 * @return array
 */
function sfparse_xml_to_array($data)
{
    if (empty($data)) {
        return array();
    }
    $xml = new SimpleXMLElement($data);
    return json_decode(json_encode($xml), true);
}

/**
 * 输出分割后的第几元素
 * @param $string string   分割字符串
 * @param string $char  分割字符
 * @param int $index    元素下表
 * @return string
 */
function sfstr_index($string, $char = ',', $index = 0)
{
    $strs = explode($char, $string);
    return $strs[$index];
}

function sfstr_safe($string, $startQty = 0, $endQty = 0, $re = '*'){
    $length = strlen($string) - $endQty - $startQty;
    if(empty($string) || empty($length) || empty($re)) return $string;
    $start = $startQty;
    $end = $start + $length;
    $strLen = mb_strlen($string);
    $strArr = array();
    for($i=0; $i<$strLen; $i++) {
        if($i>=$start && $i<$end)
            $strArr[] = $re;
        else
            $strArr[] = mb_substr($string, $i, 1);
    }
    return implode('',$strArr);
}

/**
 * 给字符串#xxx#变量赋值
 * @param string $str  需要替换的字符串
 * @param array $param    变量值 array(name=>value)
 * @return string mixed
 */
function sfstr_var($str, $param)
{
    foreach ($param as $key => $value) {
        $str = str_replace('#' . $key . '#', $value, $str);
    }
    return $str;
}
function sfstr_replace_once($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}
/**
 * 斜杠转换为参数
 * @param $url
 * @return array
 */
function sfstr_dispatch($url) {
    // Split the URL into its constituent parts.
    $parse = parse_url($url);
    // Remove the leading forward slash, if there is one.
    $path = ltrim($parse['path'], '/');
    // Put each element into an array.
    $elements = explode('/', $path);
    // Create a new empty array.
    $args = array();
    // Loop through each pair of elements.
    for( $i = 0; $i < count($elements); $i = $i + 2) {
        $args[$elements[$i]] = $elements[$i + 1];
    }
    return $args;
}

/**
 * 非空字符串链接
 * @param $value
 * @param $exp
 * @return string
 */
function sfstr_contact($value, $exp)
{
    return !empty($value) ? $value . $exp : '';
}

function sfsubstr_ellipsis($str, $end, $start = 0, $add = ' ...')
{
    $str = strip_tags($str);
    $len = mb_strlen($str, 'utf-8');
    if ($start >= 0) {
        return mb_substr($str, $start, $end, 'utf-8') . ($len > $end ? $add : '');
    } else {
        return ($len > $end ? $add : '') . mb_substr($str, $len - $end, $end, 'utf-8');
    }
}

/**
 * 对字符串执行指定次数替换
 * @param  Mixed $search   查找目标值
 * @param  Mixed $replace  替换值
 * @param  Mixed $subject  执行替换的字符串／数组
 * @param  Int   $limit    允许替换的次数，默认为-1，不限次数
 * @return Mixed
 */
function sfstr_replace_limit($search, $replace, $subject, $limit=-1){
    if(is_array($search)){
        foreach($search as $k=>$v){
            $search[$k] = '`'. preg_quote($search[$k], '`'). '`';
        }
    }else{
        $search = '`'. preg_quote($search, '`'). '`';
    }
    return preg_replace($search, $replace, $subject, $limit);
}

function sfis_valid_array_first_key($param, $config)
{
    foreach ($config as $key => $value) {
        if (is_array($value)) {
            $flag = true;
            foreach ($value as $v) {
                if (empty($v)) {
                    $flag = false;
                }
            }
            if ($flag) {
                return implode(',', $value);
            }
        } elseif (!empty($param[$value])) {
            return $value;
        }
    }
    return false;
}
/**
 * 有一个是否有效数据
 * @param $param array    数组参数
 * @param $config array   判断配置，array('id', 'no')，如果id存在有值，则返回id；array('no' => 'numeric|array|string')表示no为特定类型，则返回no；array('type' => array('YES', 'NO'))如果type符合YES或NO，则返回type
 * @return bool|mixed|string    返回有效的数组key，为false为无效
 */
function sfget_valid_one($param, $config)
{
    if (is_string($param)) {
        $param = array($param);
    }
    foreach ($config as $key => $value) {
        if (is_numeric($key)) {
            if (!empty($param[$value])) {
                return $value;
            }
        } else {
            if (is_array($value)) {
                if (in_array($param[$key], $value)) {
                    return $key;
                }
            } else {
                switch ($value) {
                    case 'numeric':
                        if (is_numeric($param[$key])) {
                            return $key;
                        }
                        break;
                    case 'array':
                        if (is_array($param[$key])) {
                            return $key;
                        }
                        break;
                    case 'string':
                        if (!is_numeric($param[$key])) {
                            return $key;
                        }
                        break;
                }
            }
        }
    }
    return false;
}

/**
 * 是否有效数组
 * @param $param array    数组参数
 * @param $config array   判断配置，array('id', 'code')表示id和code不可为空；array('no' => 'numeric|array|string')表示no必须为特定类型；array('type' => array('YES', 'NO'))表示type的值班必须为YES或则NO
 * @return bool|int|mixed|string    为true则有效，其它则为错误的key值
 */
function sfis_valid($param, $config)
{
    if (is_string($param)) {
        $param = array($param);
    }
    foreach ($config as $key => $value) {
        if (is_numeric($key)) {
            if (empty($param[$value])) {
                return $value;
            }
        } else {
            if (is_array($value)) {
                if (!in_array($param[$key], $value)) {
                    return $key;
                }
            } else {
                switch ($value) {
                    case 'numeric':
                        if (!is_numeric($param[$key])) {
                            return $key;
                        }
                        break;
                    case 'array':
                        if (!is_array($param[$key])) {
                            return $key;
                        }
                        break;
                    case 'string':
                        if (is_numeric($param[$key])) {
                            return $key;
                        }
                        break;
                }
            }
        }
    }
    return true;
}

function sfhas_url($keyword)
{
    global $curUrl;
    if (empty($curUrl)) {
        $request = \think\Container::get('request');
        $curUrl = $request->url();
    }
    if ($keyword != '/') {
        return is_numeric(strpos($curUrl, $keyword)) || is_numeric(strpos(urldecode($curUrl), $keyword));
    } else {
        if ($keyword == $curUrl) {
            return true;
        }
    }
    return false;
}

/**
 * 数组里的结果是否都有效
 * @param $array    数组数据
 * @return bool|null    都有效返回true，空数组返回null，其中一个错误则返回false
 */
function sfarray_true($array) {
    if (empty($array)) {
        return null;
    }
    foreach ($array as $key => $value) {
        if (!$value) {
            return false;
        }
    }
    return true;
}

function sfarray_key2list($array, $key)
{
    $retArr = [];
    foreach ($array as $value) {
        if (!isset($retArr[$value[$key]])) {
            $retArr[$value[$key]] = [];
        }
        $retArr[$value[$key]][] = $value;
    }
    return $retArr;
}

/**
 * 堆入（合并）数组
 * @param array $array  堆入的数组
 * @param array $push_array 需要推入的数组
 * @param array|null $only_keys 不为空则只堆入指定key的值  array('key1', 'key2', 'array_key' => 'push_array_key')
 * @param bool $rep 为空也替换
 * @param string $rep_str 为空时代替的值
 * @return array|mixed  堆入后的数组
 */
function sfarray_push(&$array, $push_array, $only_keys = null, $rep = false, $rep_value = '') {
    if (is_array($only_keys)) {
        foreach ($only_keys as $key => $value) {
            if (is_numeric($key)) {
                if ($rep) {
                    $array[$value] = $push_array[$value] ? $push_array[$value] : $rep_value;
                } else {
                    if (!empty($push_array[$value])) {
                        $array[$value] = $push_array[$value];
                    }
                }
            } else {
                if ($rep) {
                    $array[$key] = $push_array[$value] ? $push_array[$value] : $rep_value;
                } else {
                    if (!empty($push_array[$value])) {
                        $array[$key] = $push_array[$value];
                    }
                }
            }
        }
    } else {
        $array = array_merge($array, $push_array);
    }
    return $array;
}
function sfarray_has_value_qty($param)
{
    $qty = 0;
    foreach ($param as $value) {
        if (!empty($value)) {
            $qty++;
        }
    }
    return $qty;
}
/**
 * 在数组指定位置插入数组
 * @param array $array	原数组
 * @param int $position	需要插入到原数组的位置
 * @param array $insert_array	需要插入的数组
 * @return array
 */
function sfarray_insert(&$array, $position, $insert_array){
    $first_array = array_splice($array, 0, $position);
    $array = array_merge($first_array, $insert_array, $array);
    return $array;
}
/**
 * 转换md5短码
 * @param string $a    md5值
 * @return string   md5短码
 */
function sfmd5_short($a){
    for($a = md5( $a, true ),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
        $d = '',
        $f = 0;
        $f < 8;
        $g = ord( $a[ $f ] ),
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
        $f++
    ) {
        //
    }
    return $d;
}

function sfmd5_dir($name, $level = 1, $start = '')
{
    $level--;
    $path = '';
    $name = $name . '';
    if (!empty($start)) {
        $fIndex = strpos($name, $start);
        if (is_numeric($fIndex)) {
            $sLen = strlen($start);
            $path = '/' . substr($name, 0, $fIndex + $sLen);
            $name = substr($name, $fIndex + $sLen);
            $level--;
        }
    }
    for ($i = 0, $index = strlen($name); $i < $index; $i++ ) {
        if ($i > $level) {
            break;
        }
        $path .= '/' . $name[$i];
    }
    return substr($path, 1);
}

/**
 * 加密密码
 * @param $code 原始密码
 * @return string   加密后的密码  md5:XX
 */
function sfencode($code)
{
    $rand=substr(md5(rand(1, time())),rand(1, 10),2);
    $str1=substr($rand, 0,1);
    $str2=substr($rand, 1,1);
    $number=(int)(is_numeric($str1)?$str1:is_numeric($str2)?$str2:strlen($code)/2);
    $leftStr=substr($code, 0,$number);
    $rightStr=substr($code, $number);
    return md5($leftStr.$str2.$rightStr.$str1).':'.$rand;
}

/**
 * 判断密码是否正确
 * @param $code 原始用户输入的密码
 * @param $eCode    数据库加密后的密码 md5:XX
 * @return bool 有效则返回 true
 */
function sfcheck($code, $eCode)
{
    $array=explode(':', $eCode);
    if (count($array) !== 2) {
        return false;
    }
    $str1=substr($array[1], 0,1);
    $str2=substr($array[1], 1,1);
    $number=(int)(is_numeric($str1)?$str1:is_numeric($str2)?$str2:strlen($code)/2);
    $leftStr=substr($code, 0,$number);
    $rightStr=substr($code, $number);
    return md5($leftStr.$str2.$rightStr.$str1)==$array[0];
}


/**
 * 加密字符串
 * @param $str  需要加密的字符串
 * @return mixed|string 加密后的字符串
 */
function sfenstr($str)
{
    $str = @openssl_encrypt($str, "AES-256-CBC", ENDE_KEY);
    return sfurl_encode($str);
}

/**
 * 解密字符串
 * @param $str  加密后的字符串
 * @return string   解密后的字符串
 */
function sfdestr($str)
{
    $str = sfurl_decode($str);
    return trim(@openssl_decrypt($str, "AES-256-CBC", ENDE_KEY));
}

/**
 * 堆送后台临时信息(显示需模板支持，结束即销毁)
 * @param String $content   内容
 * @param String $title     标题（可选）
 */
function sfpush_admin_tmp_message($content, $title = 'Message', $level = 'normal', $data = array())
{
    @session_start();
    if (!isset($GLOBALS['admin_timestamp'])) {
        $GLOBALS['admin_timestamp'] = time();
    }
    if (isset($_SESSION['admin_tmp_messages'])) {
        $messages = $_SESSION['admin_tmp_messages'];
    }
    if (empty($messages)) {
        $messages = array();
    }
    $messages[] = array(
        'level' => $level,
        'title' => $title,
        'content' => $content,
        'data' => $data,
        'timestamp' => $GLOBALS['admin_timestamp']
    );
    $_SESSION['admin_tmp_messages'] = $messages;
}
/**
 * 获取后台临时信息
 */
function sfget_admin_tmp_messages()
{
    if (!isset($_SESSION)) {
        session_start();
    }
    if ( isset($_SESSION['admin_tmp_messages']) ) {
        $messages = $_SESSION['admin_tmp_messages'];
        unset($_SESSION['admin_tmp_messages']);
        return $messages;
    } else {
        return null;
    }
}

/**
 * 堆送后台临时信息(显示需模板支持，结束即销毁)
 * @param String $content   内容
 * @param String $title     标题（可选）
 */
function sfpush_tmp_message($content, $title = 'Message', $level = 'error')
{
    @session_start();
    if (!isset($GLOBALS['timestamp'])) {
        $GLOBALS['timestamp'] = time();
    }
    if (isset($_SESSION['tmp_messages'])) {
        $messages = $_SESSION['tmp_messages'];
    }
    if (empty($messages)) {
        $messages = array();
    }
    $messages[] = array(
        'level' => $level,
        'title' => $title,
        'content' => $content,
        'timestamp' => $GLOBALS['timestamp']
    );
    $_SESSION['tmp_messages'] = $messages;
    session_write_close();
}
/**
 * 获取后台临时信息
 */
function sfget_tmp_messages()
{
    @session_start();
    if ( isset($_SESSION['tmp_messages']) ) {
        return $_SESSION['tmp_messages'];
    } else {
        return null;
    }
}

/**
 * 输出ini格式的配置键值
 * @param $setting  配置字符串，如 OK=active;NO=empty;
 * @param $k    配置键名，如NO
 * @param $nv   如果配置为空值，则返回该值班
 * @return mixed    返回指定键值
 */
function sfini_kv($setting, $k, $nv)
{
    $datas = parse_ini_string($setting);
    return isset($datas[$k]) ? $datas[$k] : $nv;
}

/**
 * 自动转换编码为 utf-8
 * @param $data 需要转换的数据
 * @return string   转换后的字符
 */
function sfcharacet($data)
{
    if ( !empty($data) ) {
        $fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
        if ( $fileType != 'UTF-8') {
            $data = mb_convert_encoding($data ,'utf-8' , $fileType);
        }
    }
    return $data;
}

/**
 * 获取一个按照事件生成的长编号
 * @param string $added 附加字符串
 * @return string   编号
 */
function sfget_now_time_long_number($added = '')
{
    $time = time();
    $year = date('Y', $time);
    $cY = (int)substr($year, 2, 1);
    if ($cY == 0) {
        $cY = 1;
    }
    list($usec, $sec)   =   explode(' ', microtime());
    $ip = sfget_ip();
    $ip = ip2long($ip);
    $ip = sprintf('%05s', abs($ip));
    $ip = substr($ip, -5);
    return $cY . substr(date('ymdHis',$time), 1).substr($usec, 2, 2) . $ip . rand(1, 9) . $added;
}

/**
 * 获取一个唯一字符串
 * @return string   唯一字符串
 */
function sfget_unique()
{
    list($usec, $sec)   =   explode(' ', microtime());
    $s = $sec + $usec;
    $s = explode('.', $s);
    $s[1] = sprintf('%04s', $s[1]);
    $s = implode('', $s);
    return $s . sprintf('%04s', rand(0, 1000));
}

/**
 * 输出图片URL地址
 * @param $path 图片路径
 * @param null $width   图片宽度
 * @param null $height  图片高度
 * @param int $opacity  背景透明度
 * @param null $baseDir 根目录
 * @return mixed    图片URL地址
 */
function sfimg($path, $width = null, $height = null, $opacity = 127, $baseDir = null)
{
    $image =Lib_Image::instance();
    return $image->getConvertUrl($path, $width, $height, $opacity, $baseDir);
}


//加密
function sfbase64_encode($char) {
    $asciivalue = ord($char);

    //判断是否为数字
    if ($asciivalue >= 48 && $asciivalue <= 57) {
        return '99' . $char;
    }

    //判断是否为小写字母
    if ($asciivalue >= 97 && $asciivalue <= 122) {
        return '88' . $char;
    }

    $result = '';
    //判断ascii值是否为三位数，若是则直接返回，若不是则补全三位
    switch (strlen($asciivalue)) {
        case 1:
            $result = '77' . strval($asciivalue);
            break;
        case 2:
            $result = '6' . strval($asciivalue);
            break;
        case 3:
            $result = strval($asciivalue);
            break;
        default:
            break;
    }
    return $result;
}

//解密
function sfbase64_decode($strtemp) {
    $judge = substr($strtemp, 0, 2);

    $result = '';
    //判断字符串类型
    switch ($judge) {
        case '99':
        case '88':
            $result = substr($strtemp, 2, 1);
            break;
        case '77':
            $result = chr(intval(substr($strtemp, 2, 1)));
            break;
        default:
            if (substr($judge, 0, 1) == '6') {
                $result = chr(intval(substr($strtemp, 1, 2)));
            } else {
                $result = chr(intval(substr($strtemp, 2, 1)));
            }
            break;
    }
    return $result;
}


/**
 * 获取URL地址
 * @param string $path  路径
 * @param null $param   附加参数
 * @return bool|string  URL地址
 */
function sfurl($path = '/', $param = null)
{
    if ($path == './') {
        $request = \think\Container::get('request');
        $url = SITEURL_ROOT . $request->url();
    } else {
        $url = BASEDIR . $path;
        $index = strlen($url);
        if ($url[$index - 1] == '?') {
            $url = substr($url, 0, $index - 1);
        }
    }
    if (!empty($param)) {
        if (!empty($param['GET'])) {
            $urlTemp = trim($url, '/');
            if (!empty($urlTemp)) {
                $args = explode('/', $urlTemp);
                if (!empty($args) && count($args) > 4) {
                    for ($i = 5, $index = count($args); $i + 1 < $index; $i+= 2) {
                        $url = str_replace('/' . $args[$i] . '/' . $args[$i+1], '', $url);
                    }
                }
            }
            return \think\facade\Url::build($url, $param['GET'], false);
        }
    }
    return $url;
}

/**
 * URL转义
 * @param $url  url地址
 * @return mixed|string 转义后的URL地址
 */
function sfurl_encode($url)
{
    $url = base64_encode($url);
    $turl = '';
    for ($i = 0; $i < strlen($url); $i++) {
        $turl .= sfbase64_encode(substr($url, $i, 1));
    }
    $url = $turl;
    $url = str_replace('+', '_a', $url);
    $url = str_replace('/', '_b', $url);
    $url = str_replace('=', '_c', $url);
    return $url;
}

/**
 * URL反转义
 * @param $url  已转义的URL地址
 * @return bool|string  反转义后的URL地址
 */
function sfurl_decode($url)
{
    $url = str_replace('_a', '+', $url);
    $url = str_replace('_b', '/', $url);
    $url = str_replace('_c', '=', $url);
    $turl = '';
    for ($i = 0; $i < strlen($url); $i+=3) {
        $turl .= sfbase64_decode(substr($url, $i, 3));
    }
    $url = $turl;
    return base64_decode($url);
}

/**
 * 重定向URL
 * @param null $url 重定向后的URL地址
 * @param bool $redirect    是否接受参数重定向
 */
function sfredirect($url = null, $redirect = true)
{
    session_write_close();
    if ($url === null && $redirect && isset($_REQUEST['redirect'])) {
        $url = $_REQUEST['redirect'];
    }
    if (is_numeric($url)) {
        sfgo($url, false);
    } else {
        if (empty($url)) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $url = $_SERVER['HTTP_REFERER'];
            } else {
                $url = '/';
            }
            if (empty($url)) {
                $request = \think\Container::get('request');
                $url = $request->url();
            }
        } else {
            if (!is_numeric(strpos($url, ':'))) {
                $url = sfurl($url);
            }
        }
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('location:' . $url);
    }
    exit();
}

/**
 * 回退重定向
 * @param $setp 回退第几步，负数为回退，正数为前进
 * @param bool $return  是否只返回
 * @return mixed    需要回退的URL地址
 */
function sfgo($setp, $return = false)
{
    $curKey = (!defined('FLAG_ADMIN') || FLAG_ADMIN !== true) ? 'front' : 'admin';
    @session_start();
    $track = isset($_SESSION['track']) ? $_SESSION['track'] : array('map' => [], 'history' => []);
    if (!empty($track['map'][$curKey][$setp])) {
        sfredirect($track['map'][$curKey][$setp]);
    }
    $setp += 2;
    if (in_array($setp, array(0, 1, 2))) {
        $goUrl = @$track['history'][$curKey][$setp];
        if (empty($goUrl)) {
            $goUrl = current($track['history'][$curKey]);
        }
        if (!empty($track['map'][$curKey][$goUrl])) {
            $goUrl = $track['map'][$curKey][$goUrl];
        }
        $request = \think\Container::get('request');
        $url = $request->url();
        if ($url != $goUrl) {
            if ($return)
                return $goUrl;
            sfredirect($goUrl);
        }
    }
    if ($return) {
        return false;
    }
    sfredirect();
}

/**
 * 按环境输出响应客户端
 * @param int $result   响应结果
 * @param string $message   响应消息
 * @param string $data  响应数据
 * @param string $redirect  跳转页面
 */
function sfresponse($result = 1, $message = '成功', $data = '', $redirect = null)
{
    if ($result === true) {
        $result = 1;
    }
    if ($result === false) {
        $result = 0;
    }
    if (is_numeric($result)) {
        $result = $result;
    }
    $header = true;
    $ret = array(
        'code' => $result
        , 'msg' => $message
        , 'data' => $data
    );
    if (FLAG_AJAX) {
        sfquit(json_encode($ret), $header);
    }
    if (!empty($message)) {
        if (defined('FLAG_ADMIN')) {
            sfpush_admin_tmp_message($message, '', null, $data);
        } else {
            sfpush_tmp_message($message, '', null, $data);
        }
    }
    sfredirect($redirect);
}

function sfget_csv($file_path)
{
    if (is_file($file_path)) {
        setlocale(LC_ALL, 'en_US.UTF-8');
        $handle =   fopen($file_path, 'r');
        while ($line = fgetcsv($handle)) {
            if (count($line) >   0) {
                $datas[] = explode(',', sfcharacet(implode(',', $line)));
            }
        }
        fclose($handle);
        return $datas;
    } else {
        return null;
    }
}

function sfget_xls($file_path, $index = 0)
{
    if (!class_exists('Lib_Excel')) {
        include ROOT . '/application/lib/Excel.php';
    }
    return Lib_Excel::instance()->read($file_path, $index);
}

function sfsave_xls($file_path, $list, $mapHead = [])
{
    if (!class_exists('Lib_Excel')) {
        include ROOT . '/application/lib/Excel.php';
    }
    return Lib_Excel::instance()->write($file_path, $list, $mapHead);
}

function sfget_tmp_file($file_name)
{
    $dir = ROOT . '/public/static/asset/tmp/' . date('YmdH') . '/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    if (is_dir($dir)) {
        return $dir . str_replace('.', '_' . date('H_i_s') . '_' . sfmd5_short(sfget_now_time_long_number()) . '.', $file_name);
    }
    return null;
}

function sfonhour($hours)
{
    $hour = date('H');
    if (is_array($hours)) {
        sort($hours);
        foreach ($hours as $value) {
            if ($hour < $value) {
                return $value;
            }
        }
    } else if (is_numeric($hours)) {
        return $hours * ceil($hour / $hours);
    }
    return 24;
}

/**
 * 伪造方法，主要用于伪代码编写
 * @param $path 路径，/public/asset/forge下的相对路径
 * @param array $param 附加参数
 * @return array|null   结果数据
 */
function sfgetforge($path, $param = array())
{
    $whereRow = null;
    if (!empty($param)) {
        $whereRow = false;
    }
    $file = ROOT_DIR . '/public/asset/forge/' . $path . '.csv';
    if (is_file($file)) {
        $returns = array();
        $datas = Lib_IoUtils::instance()->getCsv($file);
        if (!empty($datas)) {
            $cols = $datas[0];
            for ($i = 1, $len = count($datas); $i < $len; $i++) {
                $value = $datas[$i];
                $data = array();
                foreach ($value as $k => $v) {
                    $data[$cols[$k]] = $v;
                    foreach ($param as $pk => $pv) {
                        if ($data[$pk] == $pv) {
                            $whereRow = true;
                        }
                    }
                }
                if ($whereRow) {
                    return array($data);
                }
                $returns[] = $data;
            }
        }
        if ($whereRow === null) {
            return $returns;
        }
    }
    return null;
}

/**
 * 序列输出数据
 * @param $value    需要输出的数据
 * @param bool $exit    是否直接结束
 */
function sfdump($value, $exit = false)
{
    //header("Content-type:text/html;charset=utf-8");
    echo '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body><pre>';
    print_r($value);
    echo '</pre></body></html>';
    if ($exit && !isset($_REQUEST['dump'])) {
        sfquit();
    }
}
/**
 * 清空左右空格
 * @param $value    需要清空的数据
 * @return array|string 清空后的数组
 */
function sftrim(&$value, $remove = false)
{
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            if ($v !== null) {
                if ($v === '' && $remove) {
                    unset($value[$k]);
                    continue;
                }
                if (is_array($v)) {
                    foreach ($v as $k1 => $v1) {
                        if ($v1 === '' && $remove) {
                            unset($v[$k1]);
                            continue;
                        }
                        if (is_string($v1)) {
                            $v[$k1] = trim($v1);
                        }
                    }
                    $value[$k] = $v;
                } else {
                    $v = trim($v);
                    $value[$k] = $v;
                }
            }
        }
    } else {
        $value = trim($value);
    }
    reset($value);
    return $value;
}

/**
 * 加密JSON为UTF8结果
 * @param $matchs   数组数据
 * @return false|string 加密后的字符串
 */
function sfjson_encode_ex_to_utf8($matchs)
{
    return mb_convert_encoding(pack('H4',  $matchs[1]), "UTF-8", "UCS-2BE");
}

/**
 * 加密JSON为中文可见数据
 * @param $value    数组数据
 * @return false|string|string[]|null   加密后的字符串
 */
function sfjson_encode_ex($value)
{
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        $str = json_encode($value);
        $str =  preg_replace_callback("#\\\u([0-9a-f]{4})#i", 'sfjson_encode_ex_to_utf8', $str);
        return  $str;
    } else {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 404页面
 */
function sf404()
{
    header('HTTP/1.1 404 not found!');
    include ROOT_DIR . DS . 'theme/404.html';
}

/**
 * 输出随机的字符串
 * @param $len  随机长度
 * @param null $chars   随机可选的字符集合字符串
 * @return string   随机的字符串
 */
function sfrand_string($len, $chars=null)
{
    if (is_null($chars)){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
}

function sfutf8ToUnicode($utf8_str) {
    $unicode = 0;
    $unicode = (ord($utf8_str[0]) & 0x1F) << 12;
    $unicode |= (ord($utf8_str[1]) & 0x3F) << 6;
    $unicode |= (ord($utf8_str[2]) & 0x3F);
    return dechex($unicode);
}
function sfconvertU8toGbk($str){
    $data = preg_split('/(?<!^)(?!$)/u', $str );
    $return = '';
    foreach($data as $value){
        $convertStr = mb_convert_encoding($value, "GBK", "UTF-8");
        if($convertStr === false){
            $convertStr = sfutf8ToUnicode($value);
            $convertStr = mb_convert_encoding($convertStr, "gbk//ingore", "unicode");
        }
        $return .= $convertStr;
    }
    return $return;
}

function sftoutf8($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sftoutf8($value);
        }
    } else {
        $dataOut = mb_convert_encoding($data, "GBK", "UTF-8");
        if($dataOut == false){
            //不能转gbk的字符，先转unicode,再转gbk
            $dataOut = sfconvertU8toGbk($data);
        }
        $data=$dataOut;

    }
    return $data;
}

/**
 * 是否是开发者IP地址
 * @return bool
 */
function sfis_dev_ip()
{
    $ips = explode(',', $GLOBALS['etc']['dev_ip']);
    if (in_array(sfget_ip(), $ips)) {
        return true;
    }
    return false;
}

/**
 * 获取需要开发引入的调试文件
 * @param $name 调试工具下的名称
 * @return string 需要引入的调试文件
 */
function sfdebug_file($name)
{
    $rootDir = ROOT_DIR . DS . 'shell' . DS . 'tool' . DS . 'debug' . DS;
    $isDebug = false;
    if (sfis_dev_ip()) {
        //通过开发者来访IP的判断方式
        $isDebug = true;
    }
    if (!empty($_COOKIE['dev_date'])) {
        //使用 sfenstr(date('Y-m-d')) 写入到cookie的判断方式
        $date = sfdestr($_COOKIE['dev_date']);
        if (date('Y-m-d') == date('Y-m-d', $date)) {
            $isDebug = true;
        }
    }
    if (!$isDebug && $_SESSION['_ende_key'] == 1) {
        //通过浏览器输入服务器秘钥的判断方式
        $isDebug = true;
    }
    if ($isDebug) {
        $file = $rootDir . $name . '.php';
        if (is_file($file)) {
            return $file;
        }
    }
    return $rootDir . 'empty.php';
}
function sfarr_fmt_2_get_error(&$arr, $i = 0) {
    $keys = ['_FCGI_SHUTDOWN_EVENT_', 'REMOTE_PORT', 'REQUEST_TIME_FLOAT', 'REQUEST_TIME', 'created_at', 'updated_at'];
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            if ($i > 5) {
                unset($arr[$key]);
            } else {
                sfarr_fmt_2_get_error($arr[$key], ++$i);
            }
        } else {
            if (in_array($key, $keys)) {
                unset($arr[$key]);
            }
        }
    }
    return $arr;
}
function sfget_error_code($errors)
{
    $ip = sfget_ip();
    if (isset($errors['message']) && is_numeric(strpos($errors['message'], '不存在')) && !in_array($ip, array('127.0.0.1', '::1'))) {
        return null;
    }
    sfarr_fmt_2_get_error($errors);
    $number = sfmd5_short(base64_encode(print_r($errors, true)));
    $dirMd5 = sfmd5_dir($number, 5);
    $dir = ROOT . '/public/asset/internal/report/' . $dirMd5;
    if(!is_dir($dir)){
        @mkdir($dir, 0777, true);
    }
    file_put_contents($dir . DS . $number . '.debug', print_r(array($ip, sfurl('./'), $errors), true));
    $GLOBALS['report'] = $number;
    return $number;
}
/**
 * 调试数据写入到日志文件
 * @param $content  调试数据
 * @param string $type  调试类型
 * @param null $params  附加参数
 * @return string   调试的文件路径
 */
function sfdebug($content, $type = 'default', $params = null)
{
    $time = time();
    $dir_debug = ROOT . '/public/asset/internal/debug';
    if(!is_dir($dir_debug)){
        mkdir($dir_debug, 0777, true);
    }
    $dir_debug = $dir_debug . DS . $type . DS . date('Y-m-d', $time) . DS . date('H', $time) . DS . (int)(date('i', $time) / 10);
    if(!is_dir($dir_debug)){
        mkdir($dir_debug, 0777, true);
    }
    if(isset($params['file_name'])){
        $file_name = $params['file_name'] . '.log';
    }else{
        $file_name = date('Y-m-d_H_i_s', $time) . '.log';
    }
    $file_debug = $dir_debug . DS . ip2long(sfget_ip()) . '_' . $file_name;
    $start_content = '';
    $end_content = '';
    if (isset($params['title'])) {
        $start_content = ("\r\n------------------------------------" . $params['title'] . "[Start]\r\n");
        $end_content = ("\r\n------------------------------------" . $params['title'] . "[End]\r\n");
    }
    if (isset($params['save_mode']) && strcmp($params['save_mode'], 'ser') == 0){
        file_put_contents($file_debug, $start_content . serialize($content) . $end_content, FILE_APPEND);
    } else {
        file_put_contents($file_debug, $start_content . print_r($content, true) . $end_content, FILE_APPEND);
    }
    if (!empty($params['backup'])) {
        @copy($params['backup'], $file_debug . '.bak');
    }
    return $file_name;
}
/**
 * 保存日志数据
 * @param $type 日志类型
 * @param $id   类型关键编号
 * @param $content  日志内容
 * @param null $params  附加参数
 * @return mixed    是否保存成功
 */
function sflog_default($type, $id, $content, $params = null)
{
    if (!empty($params['adminID'])) {
        $adminID = $params['adminID'];
    } else {
        $adminID = sfret('adminID');
    }
    $params = !empty($params) ? $params : array();
    $data = array_key_exists('data', $params) ? $params['data'] : null;
    $level = isset($params['level']) ? $params['level'] : null;
    $log = new \app\base\model\Log();
    $param = array(
        'no' => $id
        , 'type' => $type
        , 'level' => $level
        , 'content' => $content
        , 'data' => $data
        , 'created_id' => $adminID
    );
    if (!empty($params['object'])) {
        $param['object'] = $params['object'];
    }
    return $log->add($param);
}
function sflog($content)
{
    return sflog_default('default', 0, $content);
}
/**
 * 达到预期效果时调用的完成日志
 */
function sflog_complete($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'C'
    );
    return sflog_default($type, $id, $content, $params);
}

/**
 * 未达到预期但不必要情况下调用的警告日志
 */
function sflog_warning($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'W'
    );
    return sflog_default($type, $id, $content, $params);

}

/**
 * 可能会造成严重后果时调用的致命日志
 */
function sflog_fatal($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'F'
    );
    return sflog_default($type, $id, $content, $params);
}
/**
 * 达到预期效果时调用的完成日志
 */
function sflog_complete_in($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'C'
        , 'object' => 'I'
    );
    return sflog_default($type, $id, $content, $params);
}

/**
 * 未达到预期但不必要情况下调用的警告日志
 */
function sflog_warning_in($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'W'
        , 'object' => 'I'
    );
    return sflog_default($type, $id, $content, $params);

}

/**
 * 可能会造成严重后果时调用的致命日志
 */
function sflog_fatal_in($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'F'
        , 'object' => 'I'
    );
    return sflog_default($type, $id, $content, $params);
}

/**
 * 达到预期效果时调用的完成日志
 */
function sflog_complete_dev($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'C'
        , 'object' => 'P'
    );
    return sflog_default($type, $id, $content, $params);
}

/**
 * 未达到预期但不必要情况下调用的警告日志
 */
function sflog_warning_dev($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'W'
        , 'object' => 'P'
    );
    return sflog_default($type, $id, $content, $params);

}

/**
 * 可能会造成严重后果时调用的致命日志
 */
function sflog_fatal_dev($type, $id, $content, $data = null, $adminID = null)
{
    $params = array(
        'adminID' => $adminID
        , 'data' => $data
        , 'level' => 'F'
        , 'object' => 'P'
    );
    return sflog_default($type, $id, $content, $params);
}

/**
 * 系统专用调试异常
 * @param $msg string  错误信息
 * @param int $code 错误代码
 * @throws Exception    异常
 */
function sfexception($msg, $code = 0) {
    $debugInfo = debug_backtrace();
    $msg = date('Y-m-d H:i:s') . ' ' . $debugInfo[0]['file']. ' ('.$debugInfo[0]['line'].')： '. $msg;
    throw new Exception($msg, $code);
}

/**
 * 系统有效密令函数，生成和验证（第二参数不为空则为验证）
 * @param string $prefix    token前缀，一般是session_id
 * @param null $valid_secret 是否需要验证的secret
 * @param int $exp  secret超时时间，默认半小时
 * @return bool|mixed|string
 */
function sfsecret($prefix = '', $valid_secret = null, $exp = 1800)
{
    if ($valid_secret != null) {
        $str = sfdestr($valid_secret);
        list($pre, $secret) = explode('__', $str);
        if ($prefix == $pre && $secret + $exp > time()) {
            return true;
        } else {
            return false;
        }
    } else {
        if (empty($prefix)) {
            $prefix = session_id();
        }
        $str = $prefix . '__' . time();
        return sfenstr($str);
    }
}

function sfp($name)
{
    $prefix = \think\Db::getConfig('prefix');
    return $prefix . $name;
}

/**
 * 结束系统
 * @param null $message 结束前输出的信息
 * @param bool $header  是否输出头编码
 */
function sfquit($message = null, $header = true)
{
    @session_start();
    if (isset($_SESSION['admin_tmp_messages'])) {
        $timestamp = $_SESSION['admin_tmp_messages'];
        if (isset($GLOBALS['admin_timestamp'])) {
            $GLOBALS['admin_timestamp'] = 0;
        }
        if (!isset($GLOBALS['admin_timestamp']) || $timestamp != $GLOBALS['admin_timestamp']) {
            unset($_SESSION['admin_tmp_messages']);
        }
    }
    if (isset($_SESSION['tmp_messages'])) {
        $timestamp = $_SESSION['tmp_messages'];
        if (isset($GLOBALS['timestamp'])) {
            $GLOBALS['timestamp'] = 0;
        }
        if (!isset($GLOBALS['timestamp']) || $timestamp != $GLOBALS['timestamp']) {
            unset($_SESSION['tmp_messages']);
        }
    }
    if (isset($_SESSION['?'])) {
        unset($_SESSION['?']);
    }
    session_write_close();
    if ($message !== null) {
        if ($header) {
            header("Content-type:text/html;charset=utf-8");
        }
        echo $message;
    }
    exit();
}

/**
 * 求两个已知经纬度之间的距离,单位为米
 * @param $lon1 经度1
 * @param $lat1 纬度1
 * @param $lon2 经度2
 * @param $lat2 纬度2
 * @return float|int 米的距离
 */
function sfdiff_distance($lon1, $lat1, $lon2, $lat2){
    $radLat1=deg2rad($lat1);
    $radLat2=deg2rad($lat2);
    $radLon1=deg2rad($lon1);
    $radLon2=deg2rad($lon2);
    $a=$radLat1-$radLat2;
    $b=$radLon1-$radLon2;
    $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
    return $s;
}

function sfdesensitize($string, $start = 0, $length = 0, $re = '*') {
    if(empty($string) || empty($length) || empty($re)) return $string;
    $end = $start + $length;
    $strlen = mb_strlen($string);
    $str_arr = array();
    for($i=0; $i<$strlen; $i++) {
        if($i>=$start && $i<$end){
            $str_arr[] = $re;
        }
        else{
            $str_arr[] = mb_substr($string, $i, 1);
        }

    }
    return implode('',$str_arr);
}
//custom start
session_start();
$phpsessionid = session_id();
setcookie('PHPSESSID', $phpsessionid, time() + 3156000, '/');
//custom end