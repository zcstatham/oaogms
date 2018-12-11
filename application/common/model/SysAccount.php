<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:38
 */

namespace app\common\model;
use think\exception\ErrorException;


/**
 * Class Account 渠道、运营、系统
 * @package app\admin\model
 */
class SysAccount extends Base
{

    /**
     * 获取账户列表
     */
    private function getAccountList(){

    }

    /**
     * 获取账户信息
     */
    private function getAccountInfo($uid){
        try{
            $data = $this->where(array('uid'=>$uid))->find();

        }catch (\Exception $e) {
            // 这是进行异常捕获
            return json($e->getMessage());
        }
        return $data;
    }

    /**
     * 账户登录
     * @param string $account 账号
     * @param string $password 密码
     * @return bool|mixed 用户Id | false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function login($account = '', $password = '') {
        if (!$account) {
            return false;
        }
        $user = $this->where('account',$account)->find();
        if ( isset($user['uid']) && $user['uid'] && $user['status'] && md5($password . $user['salt']) === $user['password']) {
            /* 记录登录SESSION和COOKIES */
            $auth = array(
                'uid'             => $user['uid'],
                'account'        => $user['account'],
                'last_login_time' => $user['last_login_time'],
            );
            session('user_auth', $auth);
            session('user_auth_sign', data_auth_sign($auth));
            return $user['uid'];
        } else {
            return false;
        }
    }

    /**
     * 账户注册
     * @param $account 账号
     * @param $password 密码
     * @param $repassword 确认密码
     * @param $type 账号类型
     * @param $name 账户名称
     * @return bool 用户id | false
     */
    private function register($account, $password, $repassword, $type, $name){
        $data['account'] = $account;
        $data['salt'] = config('app.siteinfo.user_salt');
        $data['password'] = $password;
        $data['repassword'] = $repassword;
        $data['type'] = $type;
        $data['name'] = $name;
        $result = $this->validate(true)->save($data);
        if ($result) {
            $data['uid'] = $this->data['uid'];
            return $data;
        }else{
            if (!$this->getError()) {
                $this->error = "注册失败！";
            }
            return false;
        }
    }

    /**
     * 账户登出
     */
    private function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 修改账户信息
     * @param $data
     * @param bool $ischangepwd
     * @return bool
     */
    private function editInfo($data, $ischangepwd = false){
        if ($data['uid']) {
            if (!$ischangepwd || ($ischangepwd && $data['password'] == '')) {
                unset($data['salt']);
                unset($data['password']);
            }else{
                $data['salt'] = config('app.siteinfo.user_salt');
            }
            $result = $this->validate('member.edit')->save($data, array('uid'=>$data['uid']));
            if ($result) {
                return true;
            }else{
                return false;
            }
        }else{
            $this->error = "非法操作！";
            return false;
        }
    }

    /**
     * 修改账户密码
     * @param $data
     * @param bool $is_reset
     * @return bool
     */
    private function editPassword($data, $is_reset = false){
        $uid = $is_reset ? $data['uid'] : session('user_auth.uid');
        if (!$is_reset) {
            $this->checkPassword($uid,$data['oldpassword']);
            $validate = $this->validate('member.password');
            if (false === $validate) {
                return false;
            }
        }

        $data['salt'] = config('app.siteinfo.user_salt');
        return $this->save($data, array('uid'=>$uid));
    }

    /**
     * 验证账户密码
     * @param $uid
     * @param $password
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function checkPassword($uid,$password){
        if (!$uid || !$password) {
            $this->error = '原始用户UID和密码不能为空';
            return false;
        }
        $user = $this->where(array('uid'=>$uid))->find();
        if (md5($password.$user['salt']) === $user['password']) {
            return true;
        }else{
            $this->error = '原始密码错误！';
            return false;
        }
    }
}