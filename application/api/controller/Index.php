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
    protected $sid;

    private $data = [
        'code'=> 1,
        'data'=> null
    ];

    function index()
    {
        return 'hello fucker';
    }

    //访问接口
    public function login(){
        Log::record($this->request);
        if(model('User')->login($this->request->param('token'))){
            $this->data['token'] = $_POST['token'];
            if(!$this->log('login')){
                $this->data['code']=0;
            }
            return json($this->data);
        }
        $this->data['code']=-1;
        return json($this->data);
    }

    //授权接口
    public function authed(){
        $uid = model('User')->register($this->mid,$this->request->post('code'));
        if($uid === true ) {
            if(!$this->log('register')){
                $this->data['code']=0;
                return json($this->data);
            }
            return json($this->data);
        }else{
            $_POST['token'] = encrypt($uid,model('User')->scret);
            $this->login();
        }
    }

    //看广告
    public function browsead(){
        if(model('User')->login($this->request->post('token'))){
            if(!$this->log('browseAd')){
                $this->data['code']=0;
                return json($this->data);
            }
            return json($this->data);
        }
        $this->data['code']=-1;
        return json($this->data);
    }

    public function log($type){
        $data = array(
            'type' => $type,
            'uid' => model('User')->register(),
            'action_ip' => get_client_ip(),
            'aid' => $this->aid,
            'mid' => $this->mid
        );
        Log::record($data);
        return model('MiniLog')->save($data);
    }

    protected function checkDate(){
        $this->mid = decodeN($this->request->param('oao_media_id'));
        $aid = $this->request->param('oao_link_key');
        $this->aid = $aid?decodeN($aid):0;
        trace(['参数检测',$this],'info');
        if(!isset($this->mid)){
            json_error_exception('1003');
        }
    }
}