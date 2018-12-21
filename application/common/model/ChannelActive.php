<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/21
 * Time: 11:45
 */

namespace app\common\model;


class ChannelActive extends Base
{
    protected $pk = 'aid';

    public static $addList = array(
        array('name'=>'name','title'=>'活动','type'=>'text','help'=>'推广途径名称'),
        array('name'=>'sid','title'=>'渠道','type'=>'select','option'=>[],'help'=>'推广渠道名称'),
        array('name'=>'mid','title'=>'小程序','type'=>'select','option'=>[],'help'=>'推广小程序名称'),
        array('name'=>'path','title'=>'监控链接','type'=>'text','help'=>'小程序监听页面路径+自定义参数，留空默认为首页'),
    );

    public static function getKeyList()
    {
        $channel = $mini = [];
        foreach (model('SysAdmin')->field('sid,nickname')->where('sid','>','10')->all() as $v){
            $channel[$v['id']] = $v['nickname'];
        }
        foreach (model('Mini')->field('mid,name')->all() as $v){
            $mini[$v['id']] = $v['name'];
        }
        $data = ChannelActive::$addList;
        $data[1]['option'] = $channel;
        $data[2]['option'] = $mini;
        return $data;
    }

    protected function getIdAttr($value, $data){
        return $data['aid'];
    }

    protected function setIdAttr($value, $data){
        return $data['id'];
    }


}