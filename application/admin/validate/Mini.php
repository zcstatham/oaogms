<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/15
 * Time: 13:39
 */

namespace app\admin\validate;


use think\Validate;

class Mini extends Validate
{
    protected $rule = array(
        'appid'   => 'require|unique:mini',
    );
    protected $message = array(
        'username.require'    => 'appid必须',
        'username.unique'    => 'appid已存在',
    );
}