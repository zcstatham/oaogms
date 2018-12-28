<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/28
 * Time: 20:54
 */

namespace app\api\validate;


class Order
{
    protected $rule = array(
        'moeny'   => 'require|>=:1',
        'num'   => 'require|>=:1',
        'desc'   => 'require',
    );
    protected $message = array(
        'moeny.require'    => '金额必须',
        'desc.require'     => '转账描述必须',
    );
    protected $scene = array(
        'edit'     => 'email,mobile',
        'password' => 'password,repassword'
    );
}