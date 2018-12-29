<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 20:06
 */
namespace app\common\model;


use think\Db;
use think\facade\Log;

class UserOld extends Base {

    protected $table = 'oao_user';
    protected $pk = 'uid';
    protected $updateTime = 'last_login_timestamp';

    public function user_extend()
    {
        return $this->hasMany('UserExtend');
    }

    protected function getIdAttr($value, $data){
        return $data['uid'];
    }

    protected function setIdAttr($value, $data){
        return $data['id'];
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

    public function login($uid){
        if(!$this->where('uid',$uid)->find()){
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

    public function register($aid,$mid,$code){
        $mInfo = model('Mini')->get($mid);
        Log::write(['miniInfo'=>$mInfo->toArray()]);
        $wx = new \com\WxApi($mInfo['appid']);
        $wxInfo = $wx->wxLogin($code,$mInfo['appsecret']);
        Log::write(['wxInfo'=>$wxInfo]);
        if(isset($wxInfo['errcode']) && $wxInfo['errcode']!=0){
            return false;
        }
        $uinfo = $this->field('uid')->where('openid',$wxInfo['openid'])->find();
        if(!$uinfo['uid']){
            $now = date('Y-m-d H:m:s', time());
            Db::startTrans();
            try {
                $uid = db('user')->insertGetId([
                    'openid' => $wxInfo['openid'],
                    'reg_ip' => get_client_ip(),
                    'last_login_ip' => get_client_ip(),
                    'last_login_timestamp' => $now,
                    'create_timestamp' => $now
                ]);
                db('user_extend')->insert(array(
                    'uid' => $uid,
                    'mid' => $mid,
                    'reg_ip' => get_client_ip(),
                    'last_login_ip' => get_client_ip(),
                    'last_login_timestamp' => $now,
                    'create_timestamp' => $now
                ));
                db('mini_log')->insert(array(
                    'type' => 'register',
                    'uid' => $uid,
                    'action_ip' => get_client_ip(),
                    'aid' => $aid,
                    'mid' => $mid,
                    'create_timestamp' => $now
                ));
                Log::write('startTrans');
                Db::commit();
                Log::write('commit');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                Log::write('rollback');
                return false;
            }
        }
//        session('user_'.$user['uid'].'_session_key',$wxInfo);
        return $uinfo['uid'];
    }

    public function setUserInfo($info){
        $emap = [
            'uid'=>$info['id'],
            'mid'=>$info['mid'],
        ];
        $sinfo = db('user_extend')->field('status')->where($emap)->find();
        Log::write(['info'=>$info,'sql'=>$this->getLastSql(),'status'=>$sinfo['status'],'emap'=>$emap]);
        $data = array(
            'nickname'=>$info['userinfo']['nickName'],
            'avator'=>$info['userinfo']['avatarUrl'],
            'sex'=>$info['userinfo']['gender'],
        );
        db('user')
            ->data($data)
            ->where('uid',$info['id'])
            ->update();
        if((int)$sinfo['status'] != 1) {
            db('user_extend')
                ->data(['status'=>1])
                ->where($emap)
                ->update();
            return 'newAuth';
        }else{
            return 'updata';
        }
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