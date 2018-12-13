<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/13
 * Time: 10:43
 */

namespace app\common\model;


class AuthGroup extends Base
{
    public $keyList = array(
        array('name'=>'id', 'title'=>'ID', 'type'=>'hidden', 'help'=>'', 'option'=>''),
        array('name'=>'module', 'title'=>'所属模块', 'type'=>'hidden', 'help'=>'', 'option'=>''),
        array('name'=>'title', 'title'=>'用户组名', 'type'=>'text', 'help'=>'', 'option'=>''),
        array('name'=>'description', 'title'=>'分组描述', 'type'=>'textarea', 'help'=>'', 'option'=>''),
        array('name'=>'status', 'title'=>'状态', 'type'=>'select', 'help'=>'', 'option'=>array(
            0 => '禁用',
            1 => '启用'
        )),
    );
}