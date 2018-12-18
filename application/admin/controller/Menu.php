<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/14
 * Time: 10:35
 */

namespace app\admin\controller;

/**
 * Class Menu
 * @title 菜单
 * @package app\admin\controller
 */
class Menu extends Base
{

    protected $menu;

    protected $beforeActionList=['beforeMethod'];

    protected function beforeMethod(){
        $this->menu = model('Menu');
    }

    /**
     * @title 菜单列表
     * @return mixed
     */
    public function index() {
        $list  = $this->menu->order('sort asc,nid asc')->all();
        if(!empty($list)){
            $tree = new \doc\Tree();
            $list = $tree->toFormatTree($list,'title');
        }
        $data = array(
            'list'=>$list,
            'keyList'=>$this->menu->keyList
        );
        $this->setMeta('菜单列表');
        $this->assign($data);
        return $this->fetch('public/list');
    }

    /**
     * @title 编辑菜单字段
     */
    public function editable($name = null, $value = null, $pk = null) {
        if ($name && ($value != null || $value != '') && $pk) {
            $this->menu->where(array('id' => $pk))->setField($name, $value);
        }
    }

    /**
     * @title 新增菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function addMenu($pid){
        if ($this->request->isPost()) {
            $this->menu->save($this->request->param());
            if($this->menu){
                session('admin_menu_list', null);
                //记录行为
//                action_log('update_menu', 'Menu', $id, session('user_auth.sid'));
                $this->success('新增成功','admin/menu/index');
            } else {
                $this->error('新增失败');
            }
        } else {
            $this->assign('info', array('pid' => $pid));
            $menus = $this->menu->all();
            $tree  = new \doc\Tree();
            $menus = $tree->toFormatTree($menus);
            if (!empty($menus)) {
                $menus = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $menus);
            } else {
                $menus = array(0 => array('id' => 0, 'title_show' => '顶级菜单'));
            }

            $data = array(
                'Menus'    => $menus,
                'keyList' => $this->menu->keyList,
            );
            $this->assign('Menus', $menus);
            $this->assign($data);
            $this->setMeta('新增菜单');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 编辑菜单
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editMenu($id = 0) {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            try{
                $this->menu->save($data, array('nid' => $data['id']));
                $this->success('更新成功','admin/menu/index');
            }catch (\think\Exception\DbException $e){
                $this->error('更新失败：'. $e->getMessage());
            }
        } else {
            try{
                $info  = $this->menu->get($id);
                $menus =  $this->menu->order('sort asc,nid asc')->cache(true)->all();
            }catch (\think\Exception $e){
                $this->error('获取后台菜单信息错误：'.$e->getMessage());
            }
            $tree  = new \doc\Tree();
            $menus = $tree->toFormatTree($menus);
            $menus = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $menus);
            $data = array(
                'info'    => $info,
                'keyList' => $this->menu->keyList,
            );
            $this->assign($data);
            $this->setMeta('编辑后台菜单');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 删除菜单
     * @param $id
     */
    public function delMenu($id) {
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        if ($this->menu->where('nid',$id)->delete()) {
            //记录行为
//            action_log('update_menu', 'Menu', $id, session('user_auth.sid'));
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * @title 切换状态
     * @param $id
     * @param $status
     * @return bool
     */
    public function ediMenuStatus($id,$status){
        try{
            $this->menu->save(['status'=>$status],['id'=>$id]);
            $this->success("更新成功！");
        } catch (\think\Exception $e){
            $this->error('更新失败：'.$e->getMessage());
        }
        return false;
    }

    public function import($pid) {
        if ($this->request->isPost()) {
            $saveData = [];
            $postData = $this->request->param();
            $pid = $postData['pid'];
            $tree  = $postData['tree'];
            $lists = explode(PHP_EOL, $tree);
            if ($lists == array()) {
                $this->error('请按格式填写批量导入的菜单，至少一个菜单');
            }
            foreach ($lists as $key => $value) {
                $record = array_merge(array( '','','','',0,0,0,$pid),explode('|', $value));
                array_push($saveData,array(
                    'title'  => $record[0],
                    'url'    => $record[1],
                    'group'  => $record[2],
                    'tip'    => $record[3],
                    'sort'   => $record[4],
                    'hide'   => $record[5],
                    'is_dev' => $record[6],
                    'pid'    => $record[7]
                ));
            }
            try{
                $this->menu->saveAll($saveData);
            }catch (\think\Exception $e){
                $this->success('导入成功');
            }
        } else {
            $data = array(
                'keyList'=> array(
                    array('name'=>'pid','title'=>'父级菜单','type'=>'hidden'),
                    array('name'=>'tree','title'=>'所属模块','type'=>'textarea',
                        'help'=>`菜单格式：名称|链接地址|分组|提示|排序|隐藏|仅开发可见<br>
                            请按顺序填写菜单项，各项以'|'分隔，前三项位必填项，其余没有需留空<br>
                            每行一条菜单配置，添加下一条请换行`),
                ),
                'pid'=>$pid,
                'pmenu'=>$this->menu->get($pid),
            );
            $this->setMeta('批量添加');
            $this->assign($data);
            return $this->fetch('public/edit');
        }
        return false;
    }
//
//    /**
//     * @title 菜单排序
//     * @author huajie <banhuajie@163.com>
//     */
//    public function sort() {
//        if ($this->request->isGet()) {
//            $ids = input('ids');
//            $pid = input('pid');
//
//            //获取排序的数据
//            $map = array('status' => array('gt', -1));
//            if (!empty($ids)) {
//                $map['id'] = array('in', $ids);
//            } else {
//                if ($pid !== '') {
//                    $map['pid'] = $pid;
//                }
//            }
//            $list = db('Menu')->where($map)->field('id,title')->order('sort asc,id asc')->select();
//
//            $this->assign('list', $list);
//            $this->setMeta('菜单排序');
//            return $this->fetch();
//        } elseif ($this->request->isPost()) {
//            $ids = input('post.ids');
//            $ids = explode(',', $ids);
//            foreach ($ids as $key => $value) {
//                $res = db('Menu')->where(array('id' => $value))->setField('sort', $key + 1);
//            }
//            if ($res !== false) {
//                session('admin_menu_list', null);
//                return $this->success('排序成功！');
//            } else {
//                return $this->error('排序失败！');
//            }
//        } else {
//            return $this->error('非法请求！');
//        }
//    }
}