<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/28
 * Time: 12:07
 */

namespace app\common\exception;



use think\Exception;

class BaseException extends Exception
{
    public $code = 500;
    public $msg = '服务器错误';
    public $errorCode = 9999;
    protected $error_code_map;

    public function __construct($params = [])
    {
        if (!is_array($params)){
            return;  // 如果没有传入数组，那么就是使用默认的 code、msg 和 errorCode
        }
        if (array_key_exists('code',$params)){
            $this->code = $params['code'];
        }
        if (array_key_exists('errorCode',$params)){
            $this->errorCode = $params['errorCode'];
            $this->msg = $this->error_code_map[$params['errorCode']]?:'服务器错误';
        }
        if (array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }
    }

}