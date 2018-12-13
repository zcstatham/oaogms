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
     * @title 用户列表
     * @return mixed
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
     * @title 新增用户
     * @param $data
     * @return bool|mixed
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
        return false;
    }

    /**
     * @title 修改用户
     * @param $mid
     * @return bool|mixed
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
        return false;
    }

    /**
     * @title 更新用户状态
     * @return bool
     */
    private function delUser(){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $reuslt = $this->model->editInfo($data, true);

            if (false !== $reuslt) {
                $this->success('修改成功！', url('admin/user/index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->error('修改失败');
        }
        return false;
    }

    /**
     * @title 重置密码
     * @return bool
     */
    private function resetPass(){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $reuslt = $this->model->editPassword($data, true);
            if (false !== $reuslt) {
                $this->success('修改成功！', url('admin/user/index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->error('修改失败');
        }
        return false;
    }

    /**
     * @title 账户授权
     * @param $mid
     * @return bool|mixed
     * @throws \Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function authUser($mid){
        $access = model('AuthGroupAccess');
        $group  = model('AuthGroup');
        if ($this->request->isPost()) {
            $uid = input('uid', '', 'trim,intval');
            $access->where('uid', $uid)->delete();
            $group_id = input('gid', '', 'trim,intval');
            if ($group_id) {
                $add = array(
                    'uid'      => $uid,
                    'group_id' => $group_id,
                );
                $access->save($add);
            }
            $this->success("设置成功！");
        } else {
            $uid  = input('id', '', 'trim,intval');
            $row  = $group->all();
            $auth = $access->where(array('uid' => $uid))->select();
            $auth_list = array();
            foreach ($auth as $key => $value) {
                $auth_list[] = $value['group_id'];
            }
            foreach ($row as $key => $value) {
                $list[$value['module']][] = $value;
            }
            $data = array(
                'uid'       => $uid,
                'auth_list' => $auth_list,
                'list'      => $list,
            );
            $this->assign($data);
            $this->setMeta("用户分组");
            return $this->fetch();
        }
        return false;
    }

    /**
     * @title 获取用户信息
     * @param null $uid
     * @return mixed
     */
    private function getUserinfo($uid = null) {
        $uid  = $uid ? : input('id');
        //如果无UID则修改当前用户
        $uid  = $uid ? : session('user_auth.uid');
        return $this->model->get($uid);
    }

    protected function beforeMethod()
    {
        $this->model = model('SysAdmin');
    }
}