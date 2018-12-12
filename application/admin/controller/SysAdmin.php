<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/12
 * Time: 14:23
 */

namespace app\admin\controller;


class SysAdmin extends Base
{
    protected $model;

    protected $beforeActionList = [
        'beforeMethod'
    ];

    /**
     * 平台用户列表
     * @return string
     */
    public function index(){
        $param = $this->request->param();
        $map['status'] = array('=', 1);
        $order = "uid desc";
        $list  = $this->model
            ->where($map)->order($order)
            ->paginate(15, false);
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'param' => $param
        );
        $this->assign($data);
        $this->setMeta('用户列表');
        return $this->fetch();
    }

    /**
     * 新增账户
     * @param $data
     * @return mixed
     */
    private function addUser($data){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            //创建注册用户
            $result = $this->model->register($data);
            if ($result) {
                $this->success('用户添加成功！', url('admin/user/index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $data = array(
                'keyList' => $this->model->addfield,
            );
            $this->assign($data);
            $this->setMeta("添加用户");
            return $this->fetch('public/edit');
        }
    }

    /**
     * 修改账户
     * @param $mid
     * @return mixed
     */
    private function editUser($mid){
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $reuslt = $this->model->editInfo($data, true);

            if (false !== $reuslt) {
                $this->success('修改成功！', url('admin/user/index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = $this->getUserinfo();
            if(!$info){
                $this->error('不存在此用户！');
            }
            $data = array(
                'info'    => $info,
                'keyList' => $this->model->editfield,
            );
            $this->assign($data);
            $this->setMeta("编辑用户");
            return $this->fetch('public/edit');
        }
    }

    /**
     * 删除账户
     * @param $mid
     * @return mixed
     */
    private function delUser($mid){
        return $mid;
    }

    /**
     * 授权账户
     * @param $mid
     * @return mixed
     */
    private function authUser($mid){
        return $mid;
    }

    private function getUserinfo($uid = null) {
        $uid  = $uid ? : input('id');
        //如果无UID则修改当前用户
        $uid  = $uid ? : session('user_auth.uid');
        return $this->model->get($uid);
    }

    /**
     * 新增用户组
     * @param $data
     * @return mixed
     */
    private function addUserGroup($data){
        return $data;
    }

    /**
     * 修改用户组
     * @param $mid
     * @return mixed
     */
    private function editUserGroup($mid){
        return $mid;
    }

    /**
     * 删除用户组
     * @param $mid
     * @return mixed
     */
    private function delUserGroup($mid){
        return $mid;
    }

    /**
     * 授权用户组
     * @param $mid
     * @return mixed
     */
    private function authUserGroup($mid){
        return $mid;
    }

    private function authList(){

    }

    private function addAuth(){

    }

    private function editAuth(){

    }

    private function delAuth(){

    }
    protected function beforeMethod()
    {
        $this->model = model('SysAdmin');
    }
}