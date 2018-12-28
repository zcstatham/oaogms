<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/28
 * Time: 15:47
 */

namespace app\common\exception;


class HttpException extends BaseException
{
    public $code = 202;
    public $msg = '请求错误';
    public $errorCode = 2020;
    protected $error_code_map = array(
        '2020' => '请求错误',
        '2021' => '缺少必要请求参数',
        '2022' => '请求格式错误',
        '2123' => '微信服务器请求失败',
        '2124' => '请求类型错误',
    );
}