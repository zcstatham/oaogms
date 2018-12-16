<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/14
 * Time: 10:43
 */

namespace app\common\model;


class Menu extends Base
{
    protected $pk = 'nid';

    public $keyList = array(
        array('name'=>'title','title'=>'菜单名称','type'=>'text','help'=>''),
        array('name'=>'group','title'=>'菜单分组','type'=>'text','help'=>''),
        array('name'=>'url','title'=>'菜单链接','type'=>'text','help'=>''),
        array('name'=>'pid','title'=>'父类菜单Id','type'=>'text','help'=>'父级菜单'),
        array('name'=>'sort','title'=>'排序','type'=>'text','help'=>'同级有效，数值越小越靠前'),
        array('name'=>'hide','title'=>'是否隐藏','type'=>'radio','option'=>array('1'=>'是','0'=>'否'),'help'=>''),
        array('name'=>'is_dev','title'=>'仅开发可见','type'=>'radio','option'=>array('1'=>'是','0'=>'否'),'help'=>''),
        array('name'=>'status', 'title'=>'状态', 'type'=>'select', 'help'=>'', 'option'=>array(
            1 => '启用',
            0 => '禁用',
            'url' => 'admin/menu/ediMenuStatus'
        )),
        array('name'=>'options', 'title'=>'操作', 'type'=>'options', 'help'=>'', 'option'=>array(
            'line'=>array(
                1 => ['编辑','admin/menu/editMenu'],
                2 => ['删除','admin/menu/delMenu'],
            ),
            'top'=>array(
                0 => ['+ 添加菜单','admin/menu/addMenu?pid=0']
            ),
        )),
    );

    protected function getIdAttr($value, $data){
        return $data['nid'];
    }

    protected function setIdAttr($value, $data){
        return $data['id'];
    }

    protected function getOutAttr($value, $data){
        $data['id'] = $data['nid'];
        unset($data['nid']);
        return $data;
    }
}