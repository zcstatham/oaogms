<?php
namespace app\index\controller;

class Index extends Base{

    public function index()
    {
        return 'aaa';
    }

    /**
     * @title 用户登录
     */
    public function login($username = '', $password = '', $verify = '') {
        if ($this->request->isPost()) {
            if (!$username || !$password) {
                return $this->error('用户名或者密码不能为空！', '');
            }
            //验证码验证
            if(!captcha_check($verify)){
                return $this->error('验证码错误！', '');
            }

            $user = model('Admin');
            $uid  = $user->login($username, $password);
            if ($uid > 0) {
                return $this->success('登录成功！', url('admin/index/index'));
            } else {
                switch ($uid) {
                    case -1:$error = '用户不存在或被禁用！';
                        break; //系统级别禁用
                    case -2:$error = '密码错误！';
                        break;
                    default:$error = '未知错误！';
                        break; // 0-接口参数错误（调试阶段使用）
                }
                return $this->error($error, '');
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * @title 后台退出
     * @return html
     */
    public function logout() {
        $user = model('Member');
        $user->logout();
        $this->redirect('admin/index/login');
    }
}
