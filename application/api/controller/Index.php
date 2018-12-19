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
    protected $model;

    private $data = [
        'code'=> 1,
        'data'=> []
    ];

    function index()
    {
        return 'hello';
    }

//    function login()
//    {
////      $token = '';
//        $ip = $this->request->ip();
//        if ($this->request->has('code')) {
//            $uInfo = $this->request->post('info');
//            $uInfo['ip'] = $ip;
//            if (!$mInfo) {
//                json_error_exception('2001');
//            }
//            $uid = model('user')->login($this->request->post('code'), $uInfo, $mInfo,$now);
////            $token = \encrypt\EncryptService::createToken(
////                ['uid'=>$uid,'mid'=>$mInfo['m_id']],
////                60*60,
////                strcode($uid.'|'.time(),'encode'));
//        }else{
//            $token = $this->request->has('token')?$this->request->param('token'):$token;
//        }
////        $tokenInfo = \encrypt\EncryptService::checkToken($token);
////        if($tokenInfo['code'] != '200'){
////            json_error_exception('1002');
////        }
//        if ($this->request->has('type')) {
//            $type = $this->request->param('type');
//            if($type == 'channel'){
//                $scene = model('mini')->getMiniInfoByAppId($this->request->param('sceneId'));
//            }else {
//                $scene = $this->request->param('sceneId');
//            }
//            $loginLog = array(
//                'l_user_id'=>$uid,
//                'l_login_id'=> $ip,
//                'l_login_type'=> $type,
//                'l_scene_id'=> $scene,
//                'l_create_timestamp'=> $now
//            );
//            $this->request->has('remark')&&($loginLog['l_remark'] = $this->request->param('remark'));
//            $lid = model('MiniLoginLog')->addLog($loginLog);
//            $uid = model('user')->updataLog(
//                $tokenInfo['data']['data']['mid'],
//                $tokenInfo['data']['data']['uid'],$ip,$now);
//            $this->data['code'] = 1;
//            $this->data['data'] = '登录成功';
//            return $this->data;
//        }
//        json_error_exception('1003');
//    }

    //访问接口
    public function visited(){

    }

    //授权接口
    public function authed(){

    }

    //看广告
    public function browsead(){

    }

    public function log(){
        $t = $this->request->post('t');//类型
        $m = decrypt($this->request->post('m'),config('siteinfo.mini_salt'));//小程序Id
        $r = decrypt($this->request->post('r'),config('siteinfo.mini_salt'));//来源
        $o = $this->request->post('o');//openid

        $data['type'] = $t;
        $data['mid'] = $m;
        $data['ip'] = $this->request->ip();
        if(isset($r)){
            $info = model('SysAdmin')->get(decrypt($r,config('siteinfo.mini_salt')));
            $data['sid'] = $info['sid'];
            $data['remark'] = $info['nickname'];
        }else {
            $data['sid'] = 1;
        }
        switch ($t){
            case '1002'://授权
                if(!$this->model->get('uid',$o)){
                    $this->model->save($data);
                }
                $this->data['data'] = array(
                    'o' => encrypt($o,config('siteinfo.mini_salt'))
                );
                break;
            case '1003'://看广告
            case '1001'://访问
                $this->model->save($data);
                break;
        }
    }

    protected function checkDate(){
        $this->mid = decrypt($this->request->post('m'),config('siteinfo.mini_salt'));
        $this->model = model('MiniLog');
        if($this->model === false){
            json_error_exception('1003');
        }
        $this->model->setTable();
    }
}