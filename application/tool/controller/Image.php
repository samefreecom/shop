<?php
    namespace app\tool\controller;

    use think\facade\Request;

    class Image
    {
        public function uploadTemp()
        {
            $param = Request::param();
            if (!empty($_FILES['file']['tmp_name'])) {
                list($fileName, $fileExt) = explode('.', basename($_FILES['file']['name']));
                list($f, $fExt) = explode('.', basename($param['image']));
                $fileExt = strtolower($fileExt);
                if (in_array($fileExt, array('jpg', 'jpeg', 'gif', 'png'))) {
                    $md5File = md5_file($_FILES['file']['tmp_name']);
                    $path = '/public/static/asset/file/temp/' . $md5File . '.' . $fExt;
                    move_uploaded_file($_FILES['file']['tmp_name'], ROOT . $path);
                    file_put_contents(ROOT . '/public/static/asset/file/temp/' . $f . '.json', json_encode(['n' => $md5File . '.' . $fExt]));
                    sfquit('<script>parent.upload_temp_callback("' . $param['id'] . '")</script>');
                }
            }
            sfquit(0);
        }

        public function uploadTempJson()
        {
            header("Content-Type:application/json; charset=utf-8");
            header("Access-Control-Allow-Origin: *");
            $param = Request::param();
            die(file_get_contents(ROOT . '/public/static/asset/file/temp/' . $param['id'] . '.json'));
        }
        
        public function upload()
        {
            $ret = [
                'code' => 0
                , 'msg' => '未发现文件'
                , 'data' => []
            ];
            $param = Request::param();
            if (!empty($_FILES['file']['tmp_name'])) {
                list($fileName, $fileExt) = explode('.', basename($_FILES['file']['name']));
                list($f, $fExt) = explode('.', basename($_FILES['file']['name']));
                $fileExt = strtolower($fileExt);
                if (in_array($fileExt, array('jpg', 'jpeg', 'gif', 'png'))) {
                    $md5File = md5_file($_FILES['file']['tmp_name']);
                    $md5Dir = sfmd5_dir($md5File, 3);
                    $dir = '/public/static/asset/img/' . $md5Dir;
                    if (!is_dir(ROOT . $dir)) {
                        \Lib_IoUtils::instance()->createDir(ROOT . $dir);
                    }
                    $path = $dir . '/' . $md5File . '.' . $fExt;
                    if (is_file(ROOT . $path) && filesize(ROOT . $path) > 100) {
                    } else {
                        move_uploaded_file($_FILES['file']['tmp_name'], ROOT . $path);
                    }
                    $ret['code'] = 1;
                    $ret['msg'] = '';
                    $ret['data']['path'] = $path;
                    $minWidth = isset($param['min_width']) ? $param['min_width'] : null;
                    $minHeight = isset($param['min_height']) ? $param['min_height'] : null;
                    $ret['data']['min_url'] = sfimg($path, $minWidth, $minHeight);
                    $maxWidth = isset($param['max_width']) ? $param['max_width'] : null;
                    $maxHeight = isset($param['max_height']) ? $param['max_height'] : null;
                    $ret['data']['max_url'] = sfimg($path, $maxWidth, $maxHeight);
                } else {
                    $ret['msg'] = '不支持的图片类型';
                }
            }
            sfquit('<!DOCTYPE html><script type="text/javascript">parent.call_back(' . json_encode($ret) . ', "image_upload");</script>');
        }
    }