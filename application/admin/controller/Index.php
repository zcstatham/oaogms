<?php
namespace app\index\controller;

class Index extends Base{

    /**
     * 自有小程序列表
     * 总用户趋势
     * 总渠道导入量
     * 总渠道导出量
     * @return string
     */
    public function index() {
        return $this->fetch();
    }

    /**
     * 账户登录
     * @param string $username
     * @param string $password
     * @param string $verify
     * @return mixed|void
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

            $user = model('Account');
            $uid  = $user->login($username, $password);
            if ($uid) {
                return $this->success('登录成功！', url('admin/index/index'));
            } else {
                return $this->error($error, '登录失败');
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
