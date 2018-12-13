<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/13
 * Time: 11:04
 */

namespace app\admin\controller;


class Group extends Base
{

    protected $group;
    protected $rules;
    protected $map;

    protected $beforeActionList=['beforeMethod'];

    protected function beforeMethod(){
        $this->group = model('AuthGroup');
        $this->rules = model('AuthRule');
    }

    /**
     * @title 用户组列表
     * @param string $type
     * @return mixed
     */
    public function index($type = 'admin') {
        $map['module'] = $type;
        $list  = $this->group->where($map)->order('id desc')->paginate(10, false);
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'type' => $type,
        );
        $this->assign($data);
        $this->setMeta('用户组管理');
        return $this->fetch();
    }

    /**
     * @title 添加用户组
     * @return bool|mixed
     */
    private function addUserGroup($type = 'admin'){
        if ($this->request->isPost()) {
            $result = $this->group->allowField(true)->save($this->request->param());
            if ($result) {
                $this->success("添加成功！", url('admin/group/index'));
            } else {
                $this->error("添加失败！");
            }
        } else {
            $data = array(
                'info'    => array('module' => $type, 'status' => 1),
                'keyList' => $this->group->keyList,
            );
            $this->assign($data);
            $this->setMeta('添加用户组');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 修改用户组
     * @param $gid
     * @return bool|mixed
     */
    private function editUserGroup($gid){
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $result = $this->group->allowField(true)->save($param, array('id'=>$param['id']));
            if ($result) {
                $this->success("编辑成功！", url('admin/group/index'));
            } else {
                $this->error("编辑失败！");
            }
        } else {
            if (!$gid) {
                $this->error("非法操作！");
            }
            $info = $this->group->get($gid);
            $data = array(
                'info'    => $info,
                'keyList' => $this->group->keyList,
            );
            $this->assign($data);
            $this->setMeta('编辑用户组');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 删除用户组
     * @param $id
     * @return bool
     */
    private function delUserGroup($id){
        if (empty($id)) {
            $this->error("非法操作！");
        }
        $result = $this->group->save(array('status'=>0),array('id'=>$id));
        if ($result) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
        return false;
    }

    /**
     * @title 用户组授权
     * @param $id
     * @return bool|mixed
     * @throws \think\Exception\DbException
     */
    private function authUserGroup($id){
        if ($this->request->isPost()) {
            $rule = $this->request->post('rule/a', array());
            $rule_result = false;
            if ($rule) {
                $rules = implode(',', $rule);
                $rule_result = $this->group->save(['rules'=>$rules],['id'=>$id]);
            }
            if ($rule_result !== false) {
                $this->success("授权成功！", url('admin/group/index'));
            } else {
                $this->error("授权失败！");
            }
        } else {
            if (!$id) {
                $this->error("非法操作！");
            }
            $group = $this->group->get($id);
            $row = model('AuthRule')->all();
            $list = array();
            foreach ($row as $key => $value) {
                $list[$value['group']][] = $value;
            }
            $data        = array(
                'list'        => $list,
                'auth_list'   => explode(',', $group['rules']),
                'id'          => $id,
            );
            $this->assign($data);
            $this->setMeta('授权');
            return $this->fetch();
        }
        return false;
    }

    /**
     * @title 权限节点列表
     * @return mixed
     */
    public function access($type = 'admin') {
        $map['module'] = $type;
        $list  = $this->rules->where($map)->order('id desc')->paginate(15, false);
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'type' => $type,
        );
        $this->assign($data);
        $this->setMeta('权限节点');
        return $this->fetch('group/access');
    }

    /**
     * @title 更新节点
     * @param $type
     */
    public function updataNode($type) {
        try{
            $this->rules->updataNode($type);
            $this->success("更新成功！");
        } catch(\think\Exception $e){
            $this->error("更新失败：".$e->getMessage());
        }
    }

    /**
     * @title 添加节点
     * @return bool|mixed
     */
    public function addNode($type = 'admin') {
        if ($this->request->isPost()) {
            try{
                $this->rules->saveNode($this->request->param());
                $this->success("创建成功！", url('admin/group/access'));
            } catch (\think\Exception $e){
                $this->error('创建失败：'.$e->getMessage());
            }
        } else {
            $data = array(
                'info'    => array('module' => $type, 'status' => 1),
                'keyList' => $this->rules->keyList,
            );
            $this->assign($data);
            $this->setMeta('添加节点');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 修改节点
     * @param $id
     * @return bool|mixed
     */
    public function editNode($id){
        if ($this->request->isPost()) {
            try{
                $this->rules->saveNode($this->request->param());
                $this->success("更新成功！", url('admin/group/access'));
            } catch (\think\Exception $e){
                $this->error('更新失败：'.$e->getMessage());
            }
        } else {
            if (!$id) {
                $this->error("非法操作！");
            }
            $info = $this->rules->get($id);
            $data = array(
                'info'    => $info,
                'keyList' => $this->rules->keyList,
            );
            $this->assign($data);
            $this->setMeta('编辑节点');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 修改节点状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function editNodeStatus($id,$status){
        try{
            $this->rules->save(['status'=>$status],['id'=>$id]);
            $this->success("更新成功！", url('admin/group/access'));
        } catch (\think\Exception $e){
            $this->error('更新失败：'.$e->getMessage());
        }
        return false;
    }

    /**
     * @title 删除节点
     * @param $id
     */
    public function delNode($id){
        if (!$id) {
            $this->error("非法操作！");
        }
        try{
            $this->rules->where(array('id' => $id))->delete();
            $this->success("删除成功！");
        } catch (\think\Exception $e){
            $this->error('删除失败：'.$e->getMessage());
        }
    }
}