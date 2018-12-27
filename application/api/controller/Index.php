<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 21:04
 */

namespace app\api\controller;


use think\Controller;
use think\facade\Log;

class Index extends controller
{

    protected $beforeActionList = [
        'checkDate'
    ];

    protected $mid;
    protected $aid;
    protected $uid;

    private $data = [
        'code' => 1,
        'data' => null
    ];

    function index()
    {
        return 'hello fucker';
    }

    //访问接口——>登录->->返回token
    public function login()
    {
        $code = $this->request->post('code');
        if (!empty($this->uid)) {
            $info = model('User')->login($this->uid);
            if (!$this->log('login')) {
                $this->data['code'] = 0; //日志写入失败
                $this->data['data'] = 'error,try again later';
            }else {
                $this->data['code'] = 1; //登录成功
                $this->data['data'] = array(
                    'info'=>$info,
                    'token'=>$token
                );
            }
        }
        if(!$this->uid && isset($code)){
            $token = model('User')->register($this->aid,$this->mid, $code);
            if ($token === false ) {
                $this->data['code'] = 0; //日志写入失败
                $this->data['data'] = 'error,try again later';
            }else {
                $this->data['code'] = 2; //注册成功
                $this->data['data'] = $token;
            }
        } else {
            $this->data['code'] = -1; //日志写入失败
            $this->data['data'] = 'error,request must have params \'code\'';
        }
        return json($this->data);
    }

    //授权接口
    public function authed()
    {
        $info = $this->request->param();
        if(!isset($this->uid) || empty($this->uid) || !is_numeric($this->uid)) {
            $this->data['code'] = -1;
            $this->data['data'] = 'error,request must have params \'code\'';
            return json($this->data);
        }
        Log::write(['request' => $info]);
        $info['id'] = $this->uid;
        $info['mid'] = $this->mid;
        $mode = model('User')->setUserInfo($info);
        if ($mode === 'newAuth') {
            if (!$this->log('auth')) {
                $this->data['code'] = 0;
                $this->data['data'] = 'error,try again later';
                return json($this->data);
            }else {
                $this->data['data'] = 'addAuth success';
            }
        }else {
            $this->data['code'] = 2;
            $this->data['data'] = 'updata success';
        }
        Log::write(['return'=>$this->data]);
        return json($this->data);
    }

    //看广告
    public function browsead()
    {
        if (!empty($this->uid) && model('User')->login($this->uid)) {
            if (!$this->log('browseAd')) {
                $this->data['code'] = 0;
                $this->data['data'] = 'error,try again later';
            }else{
                $this->data['data'] = 'browsead success';
            }
        }else {
            $this->data['code'] = -1;
            $this->data['data'] = 'error,no user';
        }
        Log::record(['return'=>$this->data]);
        return json($this->data);
    }

    public function log($type)
    {
        $data = array(
            'type' => $type,
            'uid' => $this->uid,
            'action_ip' => get_client_ip(),
            'aid' => $this->aid,
            'mid' => $this->mid
        );
        return model('MiniLog')->save($data);
    }

    protected function checkDate()
    {
        $mid = $this->request->param('oao_media_id');
        $aid = $this->request->param('oao_link_key');
        $token = $this->request->param('token');
        if(isset($token) && !empty($token)){
            $this->uid = decrypt($token, config('siteinfo.user_secret'));
        }
        if(isset($aid)){
            $this->aid = decodeN($aid);
        }else {
            $this->aid = 0;
        }
        if (isset($mid)&& !empty($mid)) {
            $this->mid = decodeN($mid);
        }else {
            json_error_exception('1003');
        }
        Log::write([
            'request'=>$this->request->param(),
            'uid'=>$this->uid,
            'aid'=>$this->aid,
            'mid'=>$this->mid,
        ]);
    }
}