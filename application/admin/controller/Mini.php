<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\admin\controller;


class Mini extends Base
{

    protected $model;

    protected $beforeActionList=['beforeMethod'];

    protected function beforeMethod(){
        $this->model = model('Mini');
    }

    public function index(){
        return $this->miniList();
    }

    /**
     * @title 小程序列表
     * @return mixed
     */
    public function miniList($type ='own') {
        $map = [];
        $order = ['mid'=>'desc'];
        if(!IS_ROOT){
            $map['sid'] = session('user_auth.sid');
            if($type == 'channel'){
                $map['sid'] = 1;
            }
        }
        $list = $this->model->where($map)->order($order)->paginate(config('siteinfo.list_rows'), false);
        $data = array(
            'list' => $list,
            'page' => $list->render(),
            'keyList'=>$this->model->keyList
        );
        $this->setMeta('小程序列表');
        $this->assign($data);
        return $this->fetch('public/list');
    }

    /**
     * @title 新增小程序
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function addMini(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $data['sid'] = session('user_auth.sid');
            $group = model('AuthGroupAccess')->where('uid',$data['sid'])->field('group_id')->find();
            if($group&&in_array($group,config('admin_group'))){
                $data['sid'] = 1;
            }
            $this->model->save($data);
            if($this->model){
                $this->success('新增成功','admin/mini/index');
            } else {
                $this->error('新增失败');
            }
        } else {
            $data = array(
                'keyList' => $this->model->keyList,
            );
            $this->assign($data);
            $this->setMeta('新增小程序');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 编辑小程序
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editMini($id = 0) {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            try{
                $this->model->save($data, array('mid' => $data['id']));
                $this->success('更新成功','admin/mini/index');
            }catch (\think\Exception\DbException $e){
                $this->error('更新失败：'. $e->getMessage());
            }
        } else {
            try{
                $info  = $this->model->get($id);
            }catch (\think\Exception $e){
                $this->error('获取后台小程序信息错误：'.$e->getMessage());
            }
            $data = array(
                'info'    => $info,
                'keyList' => $this->model->keyList,
            );
            $this->assign($data);
            $this->setMeta('编辑后台小程序');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 删除小程序
     * @param $id
     */
    public function delMini ($id) {
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        if ($this->model->where('mid',$id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * @title 绑定小程序
     * @param $id
     * @param $status
     * @return bool
     */
    public function bindMini($id,$status){
        //绑定小程序
        return false;
    }

    /**
     * @title 小程序详细信息
     * @return bool
     */
    public function moreInfo(){
        //小程序信息
        //小程序绑定列表;
        return false;
    }
}