<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 21:04
 */

namespace app\api\controller;


use think\Controller;
use think\facade\Request;

class Index extends controller
{

    protected $beforeActionList = [
        'checkMethod'
    ];

    private $data = [
        'code'=> 1,
        'data'=> []
    ];

    function index()
    {
        return 'hello';
    }

    function login()
    {
        $token = '';
        $ip = Request::ip();
        $now = date('Y-m-d HH:mm:ss',time());
        if (Request::has('code')) {
            $mInfo = model('mini')->getMiniInfoById(Request::param('key'));
            $uInfo = Request::post('info');
            $uInfo['ip'] = $ip;
            if (!$mInfo) {
                return $this->retErr('2001');
            }
            $uid = model('user')->login(Request::post('code'), $uInfo, $mInfo,$now);
            $token = \encrypt\EncryptService::createToken(
                ['uid'=>$uid,'mid'=>$mInfo['m_id']],
                60*60,
                strcode($uid.'|'.time(),'encode'));
        }else{
            $token = Request::has('token')?Request::param('token'):$token;
        }
        $tokenInfo = \encrypt\EncryptService::checkToken($token);
        if($tokenInfo['code'] != '200'){
            return $this->retErr('1002');
        }
        if (Request::has('type')) {
            $type = Request::param('type');
            if($type == 'channel'){
                $scene = model('mini')->getMiniInfoByAppId(Request::param('sceneId'));
            }else {
                $scene = Request::param('sceneId');
            }
            $loginLog = array(
                'l_user_id'=>$uid,
                'l_login_id'=> $ip,
                'l_login_type'=> $type,
                'l_scene_id'=> $scene,
                'l_create_timestamp'=> $now
            );
            Request::has('remark')&&($loginLog['l_remark'] = Request::param('remark'));
            $lid = model('MiniLoginLog')->addLog($loginLog);
            $uid = model('user')->updataLog(
                $tokenInfo['data']['data']['mid'],
                $tokenInfo['data']['data']['uid'],$ip,$now);
            $this->data['code'] = 1;
            $this->data['data'] = '登录成功';
            return $this->data;
        }
    }

    function wxPay()
    {

    }

    protected function checkMethod(){
        if(!Request::isPost()){
            $this->error('请求格式错误，请滚蛋');
        }
    }

    function retErr($code='1000',$msg=''){
        $msg = $msg?:config('app.siteinfo.error_code'.$code);
        $this->data = array(
            'code' =>$code,
            'data'=> $msg
        );
        return $this->data;
    }
}