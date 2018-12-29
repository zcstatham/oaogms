<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/28
 * Time: 20:54
 */

namespace app\api\validate;


use think\Validate;

class Order extends Validate
{
    protected $rule = array(
        'money'   => 'require|>=:1',
        'num'   => 'require|>=:1',
        'desc'   => 'require',
    );
    protected $message = array(
        'money.require'    => '金额必须',
        'num.require'      => '数量必须',
        'desc.require'     => '转账描述必须',
    );
    protected $scene = array(
        'edit'     => 'email,mobile',
        'password' => 'password,repassword'
    );
}