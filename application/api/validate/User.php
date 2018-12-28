<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/15
 * Time: 13:39
 */

namespace app\api\validate;


use think\Validate;

class User extends Validate
{
    protected $rule = array(
        'mid'   => 'require|number',
        'code' => 'require'
    );
    protected $message = array(
        'username.require'    => '用户名必须',
        'username.unique'    => '用户名已存在',
        'email.require'    => '邮箱必须',
        'email.unique'    => '邮箱已存在',
        'mobile.unique'    => '手机号已存在',
        'password.require' => '密码必须',
        'repassword.require'    => '确认密码和密码必须一致',
    );
    protected $scene = array(
        'edit'     => 'email,mobile',
        'password' => 'password,repassword'
    );
}