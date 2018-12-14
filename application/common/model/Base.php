<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/8
 * Time: 21:48
 */

namespace app\common\model;


use think\Model;

class Base extends Model
{
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'last_login_timestamp';
    protected $createTime = 'create_timestamp';

    protected function setPasswordAttr($value, $data){
        return md5($value.$data['salt']);
    }

    protected function setUsernameAttr($value)
    {
        return strtolower($value);
    }
}