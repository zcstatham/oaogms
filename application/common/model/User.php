<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 20:06
 */
namespace app\common\model;


class User extends Base {

    protected $scret = 'ksjKDdfsd9';

    public function user_extend()
    {
        return $this->hasMany('UserExtend');
    }
    /**
     * 获取小程序用户统计信息
     * @return array('累计用户'，'新增用户'，'渠道用户')
     */
    public function getUserInfo(){
        $sum = $this->cache(true)->count();
        $newSum = $this->cache(true)->where('create_timestamp','>=',date('Y-m-d'))->count();
        $newQSum = $this->cache(true)->where(['create_timestamp',['>=',date('Y-m-d')],'qid' => ['=', 'not null']])->count();
        return [
            'sum'=> $sum,
            'newSum' => $newSum,
            'newQSum' => $newQSum
        ];
    }

    public function login($token){
        $uid = decrypt($token,$this->scret);
        if(!$this->where('uid',$uid)->find('uid')){
            return false;
        }
        $result = $this->save(array(
            'last_login_ip'=>get_client_ip(),
        ),['uid' => $uid]);
        if(!$result){
            return false;
        }
        return true;
    }

//    public function login($code,$uInfo,$mInfo,$now){
//        $wx = new \wx\wxLogin($mInfo['appid'],$mInfo['appSecret']);
//        $wxInfo = $wx->wxLogin($code);
//        if($wxInfo['errcode ']!=0){
//            return $wxInfo['errcode '];
//        }
//        try{
//            $uid = $this->where('u_openid',$wxInfo['openid'])->find('u_id');
//        }catch (\think\Exception\DbException $e){
//            return -1;
//        }
//        if($uid){
//            $this->save(array(
//                'u_last_login_ip'=>$uInfo['ip'],
//                'u_last_login_timestamp'=>$now
//            ),['u_id' => $uid]);
//            return $uid;
//        }else {
//            $uInfo['openid'] = $wxInfo['openid'];
//            $uInfo['mid'] = $mInfo['m_id'];
//            return $this->register($uInfo,$now);
//        }
//    }

    public function register($mid,$code){
        $mInfo = model('Mini')->get($mid);
        $wx = new \wx\wxLogin($mInfo['appid'],$mInfo['appsecret']);
        $wxInfo = $wx->wxLogin($code);
        if($wxInfo['errcode ']!=0){
            return $wxInfo['errcode '];
        }
        $uid = $this->where('openid',$wxInfo['openid'])->find('uid');
        if(!$uid) {
            $user = new User;
            $user->nickname = $wxInfo['nickname'];
            $user->openid = $wxInfo['avatarUrl'];
            $user->sex = $wxInfo['gender'];
            $user->reg_ip = get_client_ip();
            $user->last_login_ip = get_client_ip();
            $user->save();
            $user->user_extend()->save(array(
                'uid' => $user->uid,
                'mid' => $mid,
                'reg_ip' => $user->reg_ip,
                'last_login_ip' => $user->reg_ip,
            ));
            return true;
        }
        return $uid;
    }

    public function updataLog($mid,$uid,$ip,$now){
        try{
            $this->save([
                'ue_last_login_ip'=>$ip,
                'ue_last_login_timestamp'=>$now,
            ],['u_id' => $uid]);
            $this->user_extend()->save([
                'ue_last_login_ip'=>$ip,
                'ue_last_login_timestamp'=>$now,
            ],['u_id' => $uid,'m_id' => $mid]);
        }catch (\think\Exception\DbException $e){
            return -1;
        }
    }
}