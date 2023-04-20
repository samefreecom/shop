<?php
    namespace app\api\controller;

    use app\BaseModel;

    class Base
    {
        protected $param;
        protected $data;
        public function __construct()
        {
            $inputSource = "php://input";
            $content = file_get_contents($inputSource);
            $param = json_decode($content, true);
            if (!is_array($param)) {
                $this->response('JSON参数错误！', 400);
            }
            if (empty($param['Token'])) {
                $this->response('Token参数不可为空！', 403);
            }
            if (empty($param['Data'])) {
                $this->response('Data参数不可为空！', 404);
            }
            $baseModel = new \app\api\model\Base();
            if (!$baseModel->validUserByToken($param['Token'])) {
                $this->response('Token参数错误：' . $baseModel->getError(), 403);
            }
            $user = $baseModel->getAttr('user');
            $param['user_id'] = $user['id'];
            $this->param = $param;
        }

        protected function response($message, $code = 200, $appendParam = null)
        {
            $ret = array(
                'ResponseCode' => $code
                , 'Message' => $message
            );
            if (!empty($this->data)) {
                $ret['Data'] = $this->data;
            }
            if (is_array($appendParam)) {
                $ret = array_merge($ret, $appendParam);
            }
            sfquit(sfjson_encode_ex($ret));
        }

        protected function responseModel(BaseModel $model)
        {
            $result = empty($model->getError());
            try {
                $data = $model->getAttr('data');
                if (!empty($data)) {
                    $this->data = $data;
                }
            } catch (\Exception $e) {}
            if ($result) {
                $this->response('操作成功！');
            } else {
                $this->response($model->getError(), $model->getErrorCode());
            }
        }
    }