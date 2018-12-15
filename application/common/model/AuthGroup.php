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
            1 => '启用',
            0 => '禁用',
            'url' => 'admin/group/editUserGroupStatus'
        )),
        array('name'=>'options', 'title'=>'操作', 'type'=>'options', 'help'=>'', 'option'=>array(
            'line'=>array(
                0 => ['授权','admin/group/authUserGroup'],
                1 => ['编辑','admin/group/editUserGroup'],
                2 => ['删除','admin/group/delUserGroup'],
            ),
            'top'=>array(
                0 => ['+ 添加用户组','admin/group/addUserGroup']
            ),
        )),
    );

    protected function getIdAttr($value, $data){
        return $data['id'];
    }

    protected function setIdAttr($value, $data){
        return $data['id'];
    }
}