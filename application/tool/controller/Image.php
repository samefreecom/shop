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
    }