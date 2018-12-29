<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 20:06
 */

namespace app\common\model;


use app\common\exception\BaseException;
use encrypt\EncryptService;
use think\Db;
use think\facade\Log;

class User extends Base
{

    protected $pk = 'uid';

    public function user_extend()
    {
        return $this->hasMany('UserExtend');
    }

    protected function getIdAttr($value, $data)
    {
        return $data['uid'];
    }

    protected function setIdAttr($value, $data)
    {
        return $data['id'];
    }

    /**
     * 小程序用户登录 >> 生成或刷新token
     * @param $aid
     * @param $mid
     * @param $code
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($aid, $mid, $code)
    {
        $mInfo = model('Mini')->get($mid);
        $wx = new \wx\MiniApi($mInfo['appid'], $mInfo['appsecret'], true);
        $wxInfo = $wx->wxLogin($code);
        if (!isset($wxInfo['session_key'])) {
            return false;
        }
        //检查用户是否注册过其他游戏
        $uid = $this->field('uid')->where('openid', $wxInfo['openid'])->find();
        $uid = $uid['uid'];
        $uInfo = null;
        $info = array(
            'money' => 0,
            'data' => null,
        );
        $data = array(
            'uid' => $uid,
            'Okey' => encrypt($wxInfo['openid'], $uid . config('siteinfo.mini_salt')),
            'Skey' => encrypt($wxInfo['session_key'], $uid . config('siteinfo.mini_salt'))
        );
        //用户存在，当前游戏未注册
        if ($uid && !($uInfo = db('user_extend')
                ->field('money,data')
                ->where(array(['uid', '=', $uid], ['mid', '=', $mid]))
                ->find())
        ) {
            $this->user_extend()->save(array(
                'uid' => $uid,
                'mid' => $mid,
                'money' => 0,
                'reg_ip' => get_client_ip(),
                'last_login_ip' => get_client_ip(),
            ));
            //小程序 统计 ——register
            action('api/v2.Log/save', 'register');

        //用户不存在
        } else if (!$uid && !$uInfo) {
            $now = date('Y-m-d H:i:s', time());
            Db::startTrans();
            try {
                $uid = db('user')->insertGetId([
                    'openid' => $wxInfo['openid'],
                    'create_timestamp' => $now
                ]);
                db('user_extend')->insert(array(
                    'uid' => $uid,
                    'mid' => $mid,
                    'money' => 0,
                    'reg_ip' => get_client_ip(),
                    'last_login_ip' => get_client_ip(),
                ));
                //小程序 统计 ——register
                db('mini_log')->insert(array(
                    'type' => 'register',
                    'uid' => $uid,
                    'action_ip' => get_client_ip(),
                    'aid' => $aid,
                    'mid' => $mid,
                    'create_timestamp' => $now
                ));
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                throw new BaseException();
            }

        //用户已注册
        } else {
            $info = array(
                'money' => $uInfo['money'],
                'data' => json_decode($uInfo['money']),
            );
        }
        return array(
            'data' => $data,
            'info' => $info
        );
    }

    public function setUserInfo($info)
    {
        $emap = [
            'uid' => $info['id'],
            'mid' => $info['mid'],
        ];
        $sinfo = db('user_extend')->field('status')->where($emap)->find();
        $data = array(
            'nickname' => $info['userinfo']['nickName'],
            'avator' => $info['userinfo']['avatarUrl'],
            'sex' => $info['userinfo']['gender'],
        );
        db('user')
            ->data($data)
            ->where('uid', $info['id'])
            ->update();
        if ((int)$sinfo['status'] != 1) {
            db('user_extend')
                ->data(['status' => 1])
                ->where($emap)
                ->update();

            //小程序 统计 ——auth
            action('api/v2.Log/save','auth');
            return 'newAuth';
        } else {
            return 'updata';
        }
    }

    public function updataLog($mid, $uid, $ip, $now)
    {
        try {
            $this->save([
                'ue_last_login_ip' => $ip,
                'ue_last_login_timestamp' => $now,
            ], ['u_id' => $uid]);
            $this->user_extend()->save([
                'ue_last_login_ip' => $ip,
                'ue_last_login_timestamp' => $now,
            ], ['u_id' => $uid, 'm_id' => $mid]);
        } catch (\think\Exception\DbException $e) {
            return -1;
        }
    }

}