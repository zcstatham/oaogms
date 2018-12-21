<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 21:04
 */

namespace app\api\controller;


use think\Controller;

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
        if(model('User')->login($this->request->post('token'))){
            $this->data['token'] = $_POST['token'];
            if(!log('login')){
                $this->data['code']=0;
                return json($this->data);
            }
            return json($this->data);
        }
        $this->data['code']=-1;
        return json($this->data);
    }

    //授权接口
    public function register(){
        $uid = model('User')->register($this->mid,$this->request->post('code'));
        if($uid === true ) {
            if(!log('register')){
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
            if(!log('browseAd')){
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
            'sid' => $this->sid,
            'mid' => $this->sid
        );
        return model('MiniLog')->save($data);
    }

    protected function checkDate(){
        $this->mid = decodeN($this->request->post('oao_media_id'));
        $this->sid = decodeN($this->request->post('oao_link_key'));
        if(!isset($mid)){
            json_error_exception('1003');
        }
    }
}