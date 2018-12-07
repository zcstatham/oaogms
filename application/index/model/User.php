<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/7
 * Time: 20:06
 */
namespace app\common\model;

use think\Model;

class User extends Model {

    // 设置当前模型对应的完整数据表名称
    protected $table;
    // 设置当前模型的数据库连接
    protected $connection;

    public $addfield = array(
        array('name'=>'account','title'=>'用户名','type'=>'text','help'=>'用户名会作为默认的昵称'),
        array('name'=>'password','title'=>'密码','type'=>'password','help'=>'用户密码不能少于6位'),
        array('name'=>'repassword','title'=>'确认密码','type'=>'password','help'=>'确认密码'),
        array('name'=>'email','title'=>'邮箱','type'=>'text','help'=>'用户邮箱，用于找回密码等安全操作'),
    );

    public function getUserNum($type='user'){
        $this->table = config('table_prefix').$type;
        $this->connection =  [
            'type'        => 'mysql',
            'hostname'    => config('hostname'),
            'database'    => 'thinkphp',
            'username'    => config('username'),
            'password'    => config('password'),
            'charset'     => 'utf8',
            'prefix'      => config('table_prefix'),
            'debug'       => false,
        ];
        $sum = $this->cache(true)->count();
        $newSum = $this->cache(true)->where('create_timestamp','>=',date('Y-m-d'))->count();
        $newQSum = $this->cache(true)->where(['create_timestamp',['>=',date('Y-m-d')],'qid' => ['=', 'not null']])->count();
        return [
            'sum'=> $sum,
            'newSum' => $newSum,
            'newQSum' => $newQSum
        ];
    }

    protected function getGroupListAttr($value, $data){
        $sql = db('AuthGroupAccess')->where('uid', $data['uid'])->fetchSql(true)->column('group_id');
        return db('AuthGroup')->where('id in ('.$sql.')')->column('title', 'id');
    }

    /**
     * 用户登录模型
     * @param  string  $account [description]
     * @param  string  $password [description]
     * @return [type]            [description]
     */
    public function login($account = '', $password = '') {
        if (!$account) {
            return false;
        }
        $user = $this->where('account',$account)->find();
        if (isset($user['uid']) && $user['uid'] && $user['status'] && md5($password . $user['salt']) === $user['password']) {
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
                return -1;
        }
    }


    /**
     * 用户注册
     * @param  integer $user 用户信息数组
     */
    function register($account, $password, $repassword, $appId, $name, $isautologin = true){
        $data['account'] = $account;
        $data['salt'] = config('user_salt');
        $data['password'] = $password;
        $data['repassword'] = $repassword;
        $data['appId'] = $appId;
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

    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    public function getInfo($uid){
        $data = $this->where(array('uid'=>$uid))->find();
        return $data;
    }

//    /**
//     * 修改用户资料
//     */
//    public function editUser($data, $ischangepwd = false){
//        if ($data['uid']) {
//            if (!$ischangepwd || ($ischangepwd && $data['password'] == '')) {
//                unset($data['salt']);
//                unset($data['password']);
//            }else{
//                $data['salt'] = config('salt');
//            }
//            $result = $this->validate('member.edit')->save($data, array('uid'=>$data['uid']));
//            if ($result) {
//                return $this->extend->save($data, array('uid'=>$data['uid']));
//            }else{
//                return false;
//            }
//        }else{
//            $this->error = "非法操作！";
//            return false;
//        }
//    }
//
//    public function editpw($data, $is_reset = false){
//        $uid = $is_reset ? $data['uid'] : session('user_auth.uid');
//        if (!$is_reset) {
//            //后台修改用户时可修改用户密码时设置为true
//            $this->checkPassword($uid,$data['oldpassword']);
//
//            $validate = $this->validate('member.password');
//            if (false === $validate) {
//                return false;
//            }
//        }
//
//        $data['salt'] = config('salt');
//
//        return $this->save($data, array('uid'=>$uid));
//    }
//
//    protected function checkPassword($uid,$password){
//        if (!$uid || !$password) {
//            $this->error = '原始用户UID和密码不能为空';
//            return false;
//        }
//
//        $user = $this->where(array('uid'=>$uid))->find();
//        if (md5($password.$user['salt']) === $user['password']) {
//            return true;
//        }else{
//            $this->error = '原始密码错误！';
//            return false;
//        }
//    }
}