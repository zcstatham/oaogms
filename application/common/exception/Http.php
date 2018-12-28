<?php
namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Log;

class Http extends Handle
{
    public $code = 500;
    public $msg = '服务器错误';
    public $errorCode = 9999;
    protected $error_code_map;

    public function render(Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            return response($e->getMessage(), $e->getStatusCode());
        }

        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $return = array(
                'msg' =>$e->msg,
                'errorCode' => $e->errorCode
            );
            return json($return, $this->code);
        }

        return parent::render($e);
    }
}