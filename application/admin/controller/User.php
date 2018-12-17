<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/12
 * Time: 14:23
 */

namespace app\admin\controller;


class User extends Base
{
    protected $model;

    protected $beforeActionList = [
        'beforeMethod'
    ];

    /**
     * @title 用户列表
     * @return mixed
     */
    public function index()
    {
        $param = $this->request->param();
        $map[] = array('sid','<>', 1);
        $order = "sid desc";
        $list = $this->model
            ->where($map)->order($order)
            ->paginate(config('siteinfo.list_rows'), false);
        foreach ($list as $item){
            if($item->groupId && $item->groupId->groupInfo->title){
                $item['group_name'] = $item->groupId->groupInfo->title;
            }
        }
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
    public function addUser()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            //创建注册用户
            $valid = $this->validate($data,'User');
            if($valid!== true){
                $this->error('验证失败：'.$valid);
            }
            $result = $this->model->register($data);
            if ($result) {
                $this->success('用户添加成功！', url('admin/user/index'));
            } else {
                $this->error('添加失败！');
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
    public function editUser($id)
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $reuslt = $this->model->editInfo($data, true);
            if (false !== $reuslt) {
                $this->success('修改成功！', url('admin/user/index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $info = $this->model->get($id);
            if (!$info) {
                $this->error('不存在此用户！');
            }
            $this->model->keyList[1]['type'] = 'readonly';
            $data = array(
                'info' => $info,
                'keyList' => $this->model->editfield,
            );
            $this->assign($data);
            $this->setMeta("编辑用户");
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * 修改用户状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function editUserStatus($id,$status)
    {
        try {
            $this->model->save(['status' => $status], ['sid' => $id]);
            $this->success("更新成功！", url('admin/user/index'));
        } catch (\think\Exception $e) {
            $this->error('更新失败：' . $e->getMessage());
        }
        return false;
    }

    /**
     * @title 删除用户
     * @return bool
     */
    public function delUser($id)
    {
        //获取用户信息
        try {
            if ($this->model->get($id)) {
                $this->model->where(array('sid' => $id))->delete();
                $this->success('删除用户成功！');
            } else {
                $this->error('删除失败,此用户不存在');
            }
        } catch (\think\Exception $e) {
            $this->error('删除失败：' . $e->getMessage());
        }
        return false;
    }

    /**
     * @title 重置密码
     * @return bool
     */
    private function resetPass()
    {
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
    public function authUser($id)
    {
        $access = model('AuthGroupAccess');
        $group = model('AuthGroup');
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $access->where('uid', $data['id'])->delete();
            if ( $data['id']) {
                $add = array(
                    'uid' => $data['id'],
                    'group_id' => $data['admin'],
                );
                $result = $access->save($add);
            }
            $result && $this->success("设置成功！",'admin/user/index');
        } else {
            $row = $group->all();
            $auth = $access->where(array('uid' => $id))->select();
            $auth_list = array();
            foreach ($auth as $key => $value) {
                $auth_list[] = $value['group_id'];
            }
            foreach ($row as $key => $value) {
                $list[$value['module']][] = $value;
            }
            $data = array(
                'userInfo' => $this->model->get($id),
                'auth_list' => $auth_list,
                'list' => $list,
            );
            $this->assign($data);
            $this->setMeta("用户分组");
            return $this->fetch();
        }
        return false;
    }

    protected function beforeMethod()
    {
        $this->model = model('SysAdmin');
    }
}