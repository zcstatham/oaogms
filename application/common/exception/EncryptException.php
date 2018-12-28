<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/28
 * Time: 12:07
 */

namespace app\common\exception;


class EncryptException extends BaseException
{
    public $code = 401;
    public $msg = '未经授权';
    public $errorCode = 4010;
    protected $error_code_map = array(
        '4010' => '未授权访问',
        '4410' => '签名不正确',
        '4411' => '签名尚未启用',
        '4412' => '签名已过期',
    );
}