<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:38
 */

namespace app\common\model;


use think\facade\Log;

class Mini extends Base
{

    protected $pk = 'mid';

    public $keyList = array(
        array('name'=>'id','title'=>'序号','type'=>'hidden'),
        array('name'=>'name','title'=>'名称','type'=>'text','help'=>''),
        array('name'=>'type','title'=>'类型','type'=>'radio','option'=>array('1'=>'小程序','0'=>'小游戏'),'help'=>''),
        array('name'=>'appid','title'=>'appId','type'=>'text','help'=>''),
        array('name'=>'remark','title'=>'描述','type'=>'text','help'=>''),
        array('name'=>'options', 'title'=>'操作', 'type'=>'options', 'help'=>'', 'option'=>array(
            'line'=>array(
                0 => ['详细','admin/mini/moreInfo'],
                1 => ['编辑','admin/mini/editMini'],
                2 => ['删除','admin/mini/delMini'],
            ),
            'top'=>array(
                0 => ['+ 添加小程序','admin/mini/addMini']
            ),
        )),
    );

    protected function getIdAttr($value, $data){
        return $data['mid'];
    }

    protected function setIdAttr($value, $data){
        return $data['id'];
    }

    public function channelInfo()
    {
        return $this->hasOne('SysAdmin','sid','sid');
    }

    public function bindInfo()
    {
        return $this->hasMany('MiniExtend','mid','mid');
    }
}