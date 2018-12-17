<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/8
 * Time: 22:32
 */

/**
 * 自有小程序列表
 * @return string
 */
function own(){
    return 'own';
}

/**
 * 渠道小程序列表
 * @return string
 */
function channel(){
    return 'channel';
}

/**
 * 获取小程序组
 */
function getMiniGroup(){
    $sid = session('user_auth.sid');
    if(is_administrator()){
        return 1;
    }
    $group = model('AuthGroupAccess')->where('uid',session('user_auth.sid'))->field('group_id')->find()->getData('group_id');
    if(in_array($group,config('siteinfo.admin_group')) ){
        return 1;
    }else {
        return $sid;
    }
}