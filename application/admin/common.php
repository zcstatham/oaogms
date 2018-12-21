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
 * 今日概况
 * @param $id
 * @return array
 */
function profile($map){
    $list = db('mini_log')
        ->where($map)
        ->field(['aid','type','remark','IFNULL(COUNT(*),0)'=>'count'])
        ->group('aid,type')->select();
    $charts = config('siteinfo.charts');
    foreach ($charts as $i=>$v){
        $data[$i] = array('total'=>0, 'oao'=>0, 'channel'=>0);
    }
    foreach ($list as $item){
        foreach ($charts as $i=>$v){
            !isset($data[$i]['title']) && ($data[$i]['title'] = $v);
            if($item['type'] == $i){
                $data[$i]['total'] += $item['count'];
                if($item['aid'] == 0){
                    $data[$i]['oao'] += $item['count'];
                }else{
                    $data[$i]['channel'] += $item['count'];
                }
            }
        }
    }
    return $data;
}

function getSurveyParam($mid,$sid){
    $entrystr = 'oao_media_id='.encodeN($mid,config('siteinfo.m_param_salt')).'&oao_link_key='.encrypt($sid,config('siteinfo.s_param_salt'));
    return $entrystr;
}

function getDateMap($str){
    $timeFormat = '%Y-%m-%d';
    switch ($str){
        case 'yesterday':
            $dateMap = ['create_timestamp','between',[date('Y-m-d',strtotime("-1 day")),date('Y-m-d',time())]];
            break;
        case 'week':
            $dateMap = ['create_timestamp','between',[date('Y-m-d',strtotime("-7 day")),date('Y-m-d',time())]];
            break;
        case 'month':
            $dateMap = ['create_timestamp','between',[date('Y-m-d',strtotime("-30 day")),date('Y-m-d',time())]];
            break;
        case 'today':
            $dateMap = ['create_timestamp','between',[date('Y-m-d 00:00:00',time()),date('Y-m-d H:m:s',time())]];
            $timeFormat = '%H:00:00';
            break;
        default:
            $date = implode('|',$str);
            $dateMap = ['create_timestamp','between',$date];
    }
    return array($dateMap, $timeFormat);
}

/**
 * 获取用户类型
 */
function getUserType(){
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