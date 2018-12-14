<?php
namespace app\admin\controller;

class Index extends Base{

    /**
     * @title 自有小程序列表
     * 总用户趋势
     * 总渠道导入量
     * 总渠道导出量
     * @return string
     */
    public function index() {
        return $this->fetch();
    }

    /**
     * @title 账户登录
     * @param string $username
     * @param string $password
     * @param string $verify
     * @return mixed
     */
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
                $this->success('登录成功！', url('admin/index/index'));
            } else {
                $this->error( '登录失败');
            }
        } else {
            return $this->fetch();
        }
        return false;
    }

    /**
     * @title 后台退出
     */
    public function logout(){
        model('SysAdmin')->logout();
        $this->redirect('admin/index/login');
    }
}
