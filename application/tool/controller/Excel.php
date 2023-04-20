<?php
    namespace app\tool\controller;

    use think\facade\Request;

    class Excel
    {
        public function uploadTemp()
        {
            $param = Request::param();
            if (!empty($_FILES['file']['tmp_name'])) {
                list($fileName, $fileExt) = explode('.', basename($_FILES['file']['name']));
                list($f, $fExt) = explode('.', basename($param['excel']));
                $fileExt = strtolower($fileExt);
                if (in_array($fileExt, array('xls', 'xlsx'))) {
                    $data = sfget_xls($_FILES['file']['tmp_name']);
                    file_put_contents(ROOT . '/public/static/asset/file/temp/' . $f . '.json', sfjson_encode_ex($data));
                    sfquit('<script>parent.upload_temp_callback("' . $param['excel'] . '")</script>');
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