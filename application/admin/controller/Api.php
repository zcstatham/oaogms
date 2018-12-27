<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2017/4/14
 * Time: 下午3:14
 */
namespace app\common\controller;
use think\Cache;
use think\Config;
use think\Controller;
use think\Request;
use think\Log;
class Api {

    protected $data;
    protected $param = array();
    public $params = array();

    public function __construct() {
        define('base_url',"https://api.ssdtt.com");
        $this->data = array('code' => 0, 'msg' => '请求成功', 'time' => time());
        Log::init(['type'=>'File','path'=>WEB_PATH . '/runtime/Logs/response/']);
        self::checkToken();
        $this->params = $this->checkParams(Request::instance()->action());
//        Log::write($this->params,"info");
    }


    protected function checkToken()
    {
        $request = Request::instance();
        #获取不需要验证的方法
        $config = Config::get('verify_api_list');
        $controller = $request->controller();
        $action     = $request->action();
        $actionName=  strtolower("/".$controller."/".$action);

        if(!in_array($actionName,$config))
        {
            return true;
        }


        $token  = getToken();
        if(empty($token)){
            json_error_exception(6007);
        }
        $uid = Cache::get('token_'.$token['token']);

        if(trim($uid) == '')
        {
            json_error_exception(6007);
        }

        #获取token登录信息
        $user_login_info = model('Logininfomation')->where(array('login_infomation_uid'=>$uid,'login_infomation_token'=>$token['token']))->find();
        #登录状态
        if(empty($user_login_info) || $user_login_info['login_infomation_status'] == 2){
            json_error_exception(6007);
        }

        if($token['isValid'] === false){
            json_error_exception(6007);
        }
        return true;
    }

    /**
     * 检查用户是否存在
     * @param string $type
     * @param string $value
     * @param bool $isSet
     * @param bool $check
     */
    public function checkUser($type = 'mobile' , $value = '' ,$isSet = true , $check = true)
    {
        switch ($type)
        {
            case "mobile" :
                $where[] = "mobile ='".$value."'";
                break;
            case "uid" :
                $where[] = "uid =".$value;
                break;
        }
        //获取用户信息
        $user   = model('User') -> _getOneUser($where);
        if($check){
            if($isSet){
                //用户不存在返回
                if(!$user)
                {
                    json_error_exception(6001);
                }
            }else{
                //重复注册返回
                if($user)
                {
                    json_error_exception(6002);
                }
            }
        }
        return $user;
    }

    /**
     * 检查方法参数
     *
     * @return array|mixed
     * @author ning
     */
    protected function checkParams($action = '')
    {

        $action = strtolower($action);
        $params = array();
        $allRules = $this->param;
        if (!is_array($allRules) || empty($allRules)) {
            return $params;
        }
        $allRules = array_change_key_case($allRules, CASE_LOWER);

        $request = Request::instance();
        $params  = $request->post('request') ? json_decode($request->post('request'),true) : array();
        if(!is_array($params))
        {
            json_error_exception(1001);
        }

        if(!isset($allRules[$action]) || (isset($allRules[$action]) && !is_array($allRules[$action])))
        {
            json_error_exception(1001);
        }
        foreach ($allRules[$action] as $k=>$v)
        {
            if($v['require'] && (!in_array($v['name'],array_keys($params)) || !$params[$v['name']]))
            {
                json_error_exception(1002,$v['desc']);
            }
        }
//        if($params['uid'] == 474)
//        {
//            $params['uid'] = 466;
//        }
        return $params;
    }

}