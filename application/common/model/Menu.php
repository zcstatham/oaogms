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
    public $keyList = array(
        array('name'=>'title','title'=>'菜单名称','type'=>'text','help'=>''),
        array('name'=>'tip','title'=>'菜单描述','type'=>'text','help'=>''),
        array('name'=>'group','title'=>'菜单分组','type'=>'text','help'=>''),
        array('name'=>'url','title'=>'菜单链接','type'=>'text','help'=>''),
        array('name'=>'pid','title'=>'父类菜单Id','type'=>'text','help'=>'父级菜单'),
        array('name'=>'sort','title'=>'排序','type'=>'text','help'=>'同级有效，数值越小越靠前'),
        array('name'=>'hide','title'=>'是否隐藏','type'=>'radio','option'=>array('1'=>'是','0'=>'否'),'help'=>''),
        array('name'=>'is_dev','title'=>'仅开发可见','type'=>'radio','option'=>array('1'=>'是','0'=>'否'),'help'=>''),
        array('name'=>'status','title'=>'菜单状态','type'=>'radio','option'=>array('1'=>'启用','0'=>'禁用'),'help'=>''),
    );
}