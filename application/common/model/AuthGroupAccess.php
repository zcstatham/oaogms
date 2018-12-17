<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/13
 * Time: 10:42
 */

namespace app\common\model;


class AuthGroupAccess extends Base
{
    public function groupInfo()
    {
        return $this->hasOne('AuthGroup','id','group_id');
    }
}