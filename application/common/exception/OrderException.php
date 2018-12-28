<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/28
 * Time: 20:59
 */

namespace app\common\exception;


class OrderException extends BaseException
{
    public $code = 202;
    public $msg = '订单错误';
    public $errorCode = 3020;
    protected $error_code_map = array(
        '3020' => '订单错误',
        '3021' => '缺少必要请求参数',
        '3022' => '提现金额必须大于起提金额',
        '3120' => '微信服务器请求失败',
    );
}