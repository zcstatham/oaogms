<?php
namespace app\admin\controller;

use think\facade\Cache;
use think\facade\Log;

/**
 * Class Index
 * @title 首页
 * @package app\admin\controller
 */
class Index extends Base{

    /**
     * @title 首页
     * @return mixed
     */
    public function index() {
        return $this->fetch();
    }

    /**
     * @title 清除缓存
     * @return mixed
     */
    public function clear(){
        if ($this->request->isPost()) {
            $clear = $this->request->post('clear');
            foreach ($clear as $key => $value) {
                if ($value == 'cache') {
                    Cache::clear(); // 清空缓存数据
                } elseif ($value == 'log') {
                    Log::clear();
                }
            }
            $this->success("更新成功！", url('admin/index/index'));
        } else {
            $keylist = array(
                array('name' => 'clear', 'title' => '更新缓存', 'type' => 'checkbox', 'help' => '', 'option' => array(
                    'cache' => '缓存数据',
                    'log'   => '日志数据',
                ),
                ),
            );
            $data = array(
                'keyList' => $keylist,
            );
            $this->assign($data);
            $this->setMeta("更新缓存");
            return $this->fetch('public/edit');
        }
        return false;
    }

    public function login() {
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $verify = $this->request->post('verify');
            if (!$username || !$password) {
                $this->error('用户名或者密码不能为空！', '');
            }
//            //验证码验证
//            if(!captcha_check($verify)){
//                $this->error('验证码错误！', '');
//            }

            $uid  = model('SysAdmin')->login($username, $password);
            if ($uid) {
                $url = $uid > 10? 'admin/publice/channel':'admin/index/index';
                $this->success('登录成功！', $url);
            } else {
                $this->error( '登录失败');
            }
        } else {
            return $this->fetch();
        }
        return false;
    }

    public function logout(){
        model('SysAdmin')->logout();
        $this->redirect('admin/index/login');
    }
}
