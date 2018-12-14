<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:38
 */

namespace app\common\model;

use think\exception\ErrorException;
use think\Model;


/**
 * Class username 渠道、运营、系统
 * @package app\admin\model
 */
class SysAdmin extends Base
{

    protected $insert = ['username', 'status' => 0];

    public $editfield = array(
        array('name'=>'uid','type'=>'hidden'),
        array('name'=>'username','title'=>'用户名','type'=>'readonly','help'=>''),
        array('name'=>'nickname','title'=>'昵称','type'=>'text','help'=>''),
        array('name'=>'password','title'=>'密码','type'=>'password','help'=>'为空时则不修改'),
        array('name'=>'sex','title'=>'性别','type'=>'select','option'=>array('0'=>'保密','1'=>'男','2'=>'女'),'help'=>''),
        array('name'=>'email','title'=>'邮箱','type'=>'text','help'=>'用户邮箱，用于找回密码等安全操作'),
        array('name'=>'qq','title'=>'QQ','type'=>'text','help'=>''),
        array('name'=>'score','title'=>'用户积分','type'=>'text','help'=>''),
        array('name'=>'signature','title'=>'用户签名','type'=>'textarea','help'=>''),
        array('name'=>'status','title'=>'状态','type'=>'select','option'=>array('0'=>'禁用','1'=>'启用'),'help'=>''),
    );

    public $addfield = array(
        array('name'=>'username','title'=>'用户名','type'=>'text','help'=>'用户名会作为默认的昵称'),
        array('name'=>'password','title'=>'密码','type'=>'password','help'=>'用户密码不能少于6位'),
        array('name'=>'repassword','title'=>'确认密码','type'=>'password','help'=>'确认密码'),
        array('name'=>'email','title'=>'邮箱','type'=>'text','help'=>'用户邮箱，用于找回密码等安全操作'),
    );

    public $useredit = array(
        array('name'=>'uid','type'=>'hidden'),
        array('name'=>'nickname','title'=>'昵称','type'=>'text','help'=>''),
        array('name'=>'sex','title'=>'性别','type'=>'select','option'=>array('0'=>'保密','1'=>'男','2'=>'女'),'help'=>''),
        array('name'=>'email','title'=>'邮箱','type'=>'text','help'=>'用户邮箱，用于找回密码等安全操作'),
        array('name'=>'mobile','title'=>'联系电话','type'=>'text','help'=>''),
        array('name'=>'qq','title'=>'QQ','type'=>'text','help'=>''),
        array('name'=>'signature','title'=>'用户签名','type'=>'textarea','help'=>''),
    );

    public $userextend = array(
        array('name'=>'company','title'=>'单位名称','type'=>'text','help'=>''),
        array('name'=>'company_addr','title'=>'单位地址','type'=>'text','help'=>''),
        array('name'=>'company_contact','title'=>'单位联系人','type'=>'text','help'=>''),
        array('name'=>'company_zip','title'=>'单位邮编','type'=>'text','help'=>''),
        array('name'=>'company_depart','title'=>'所属部门','type'=>'text','help'=>''),
        array('name'=>'company_post','title'=>'所属职务','type'=>'text','help'=>''),
        array('name'=>'company_type','title'=>'单位类型','type'=>'select', 'option'=>'', 'help'=>''),
    );
    /**
     * username查询器
     * @param Model $query
     * @param $value
     * @param int $type 0:完全匹配 1:前匹配 2:后匹配 3:模糊匹配
     */
    public function searchUsernameAttr(Model $query, $value, $type = 0)
    {
        switch ($type) {
            case 0:
                $query->where('username', '=', $value);
                break;
            case 1:
                $query->where('username', 'like', $value . '%');
                break;
            case 2:
                $query->where('username', 'like', '%' . $value);
                break;
            case 3:
                $query->where('username', 'like', '%' . $value . '%');
                break;
            default:
                $query->where('username', '=', $value);
        }
    }

    public function searchCreateTimestampAttr(Model $query, $value)
    {
        $query->whereBetweenTime('create_timestamp', $value[0], $value[1]);
    }

    /**
     * 获取账户列表
     */
    private function getUserList($arrayMap, $order)
    {
        try {
            return $this->where($arrayMap)->order($order)->find();
        } catch (\think\Exception $e) {
            trace('数据库操作失败：' . $e->getMessage(), 'error');
            return -1;
        }
    }

    /**
     * 获取账户信息
     */
    private function getUserInfo($sid)
    {
        try {
            return $this->get($sid);
        } catch (\think\Exception $e) {
            trace('数据库操作失败：' . $e->getMessage(), 'error');
            return -1;
        }
    }

    /**
     * 账户登录
     * @param string $username 账号
     * @param string $password 密码
     * @return bool|mixed 用户Id | false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($username = '', $password = '')
    {
        if (!$username) {
            return false;
        }
        $user = $this->where([
            'username'	 =>	$username,
        ])->find();
        if (isset($user->sid) && $user->sid && $user->status && md5($password . $user->salt) === $user->password) {

            $user->save([
                'login_times' => array('inc', 'login_times', 1),
                'last_login_ip' => get_client_ip()
            ],['sid' => $user->sid]);

            /* 记录登录SESSION和COOKIES */
            $auth = array(
                'sid' => $user->sid,
                'username' => $user->username,
                'last_login_timestamp' => $user->last_login_timestamp,
            );

            session('user_auth', $auth);
            session('user_auth_sign', data_auth_sign($auth));
            /* 记录登录SESSION和COOKIES */
            return $user->sid;
        } else {
            return false;
        }
    }

    /**
     * 账户注册
     * @param $username **账号
     * @param $password *密码
     * @param $repassword *确认密码
     * @param $type *账号类型
     * @param $name *账户名称
     * @return bool 用户id | false
     */
    public function register($data)
    {
        try {
            $addfield = [];
            foreach($data as $key=>$value) {
                $addfield[$key] = $data[$value];
            }
            $addfield['salt'] = rand_string(6);
            $this->save($addfield);
            return $this->sid;
        }catch (\think\Exception $e){
            trace('数据库操作失败：' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 账户登出
     */
    public function logout()
    {
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 修改账户信息
     * @param $data
     * @param bool $ischangepwd
     * @return bool
     */
    public function editInfo($data, $ischangepwd = false)
    {
        if ($data['sid']) {
            if (!$ischangepwd || ($ischangepwd && $data['password'] == '')) {
                unset($data['salt']);
                unset($data['password']);
            } else {
                $data['salt'] = rand_string(6);
            }
            try {
                $this->save($data,['sid' => $data['sid']]);
                return true;
            }catch (\think\Exception $e){
                trace('数据库操作失败：' . $e->getMessage(), 'error');
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 修改账户密码
     * @param $data
     * @param bool $is_reset
     * @return bool
     */
    public function editPassword($data, $is_reset = false)
    {
        $sid = $is_reset ? $data['sid'] : session('user_auth.sid');
        if (!$is_reset && !($checkPass = $this->checkPassword($sid, $data['oldpassword']))) {
            return false;
        }else if($is_reset){
            $data['password'] = '123456';
        }
        $data['salt'] = rand_string(6);
        return $this->save($data, array('sid' => $sid));
    }

    /**
     * 验证账户密码
     * @param $sid
     * @param $password
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function checkPassword($sid, $password)
    {
        if (!$sid || !$password) {
            $this->error = '原始用户sid和密码不能为空';
            return false;
        }
        try{
            $this->get($sid);
            if (md5($password . $this->salt) === $this->password) {
                return true;
            } else {
                $this->error = '原始密码错误！';
                return false;
            }
        }catch (\think\Exception $e){
            trace('数据库操作失败：' . $e->getMessage(), 'error');
            return false;
        }
    }
}