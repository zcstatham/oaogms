<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 19:19
 */

namespace app\admin\controller;

use think\Controller;

class Base extends Controller{
    protected $middleware = [
        'Menu' 	=> ['except' => ['login','logout'] ],
    ];

    /**
     * 初始化验证
     * 验证登录
     * 验证权限
     * 设置菜单
     */
   /* public function _initialize() {
        $this->param = $this->request->param();
        $this->url_path = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
        define('IS_ROOT', is_administrator());
        $this->setMenu();

        //判断登录且不在验证url里
//        if (!is_login() and !in_array($this->url_path, array('admin/index/login', 'admin/index/logout', 'admin/index/verify'))) {
//            $this->redirect('admin/index/login');
//        }
//
//        if (!in_array($this->url_path, array('admin/index/login', 'admin/index/logout', 'admin/index/verify'))) {
//            // 是否是超级管理员
//            define('IS_ROOT', is_administrator());
//            // 检测系统权限
//            if (!IS_ROOT) {
//                $access = $this->accessControl();
//                if (false === $access) {
//                    $this->error('403:禁止访问');
//                } elseif (null === $access) {
//                    $dynamic = $this->checkDynamic(); //检测分类栏目有关的各项动态权限
//                    if ($dynamic === null) {
//                        //检测访问权限
//                        if (!$this->checkRule($this->url_path, array('in', '1,2'))) {
//                            $this->error('未授权访问!');
//                        } else {
//                            // 检测分类及内容有关的各项动态权限
//                            $dynamic = $this->checkDynamic();
//                            if (false === $dynamic) {
//                                $this->error('未授权访问!');
//                            }
//                        }
//                    } elseif ($dynamic === false) {
//                        $this->error('未授权访问!');
//                    }
//                }
//            }
//            //菜单设置
//            $this->setMenu();
//        }
    }*/

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function checkRule($rule, $type = AuthRule::rule_url, $mode = 'url') {
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \author\Auth();
        }
        if (!$Auth->check($rule, session('user_auth.uid'), $type, $mode)) {
            return false;
        }
        return true;
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则表示权限不明
     *
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    protected function checkDynamic() {
        if (IS_ROOT) {
            return true; //管理员允许访问任何页面
        }
        return null; //不明,需checkRule
    }

    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function accessControl() {
        $allow = config('siteinfo.allow_visit');
        $deny = config('siteinfo.deny_visit');
        $check = strtolower($this->request->controller() . '/' . $this->request->action());
        if (!empty($deny) && in_array_case($check, $deny)) {
            return false; //非超管禁止访问deny中的方法
        }
        if (!empty($allow) && in_array_case($check, $allow)) {
            return true;
        }
        return null; //需要检测节点权限
    }

    protected function setMenu() {
        $hover_url  = $this->request->module() . '/' . $this->request->controller();
        $controller = $this->url_path;
        $menu       = array(
            'main'  => array(),
            'child' => array(),
        );
        $map['pid']  = 0;
        $map['hide'] = 0;
        $map['type'] = 'admin';
        if (!config('siteinfo.develop_mode')) {
            // 是否开发者模式
            $map['is_dev'] = 0;
        }
        $row = db('menu')->field('nid,title,url,icon,"" as style')->where($map)->order('sort asc')->select();
        foreach ($row as $key => $value) {
            //此处用来做权限判断
            if (IS_ROOT || $this->checkRule($value['url'], 2, null) || 'test') {
                if ($controller == $value['url']) {
                    $value['style'] = "active";
                }
                $menu['main'][$value['nid']] = $value;
            }
        }

        // 查找当前子菜单
        $pid = db('menu')->where("pid !=0 AND url like '%{$hover_url}%'")->value('pid');
        $id  = db('menu')->where("pid = 0 AND url like '%{$hover_url}%'")->value('nid');
        $pid = $pid ? $pid : $id;
//        if (strtolower($hover_url) == 'admin/content' || strtolower($hover_url) == 'admin/attribute') {
//            //内容管理菜单
//            $pid = db('menu')->where("pid =0 AND url like '%admin/category%'")->value('id');
//        }
        if ($pid) {
            $map['pid']  = $pid;
            $map['hide'] = 0;
            $map['type'] = 'admin';
            $row         = db('menu')->field("nid,title,url,icon,`group`,pid,'' as style")->where($map)->order('sort asc')->select();
            foreach ($row as $key => $value) {
                if (IS_ROOT || $this->checkRule($value['url'], 2, null) || 'test') {
                    if ($controller == $value['url']) {
                        $menu['main'][$value['pid']]['style'] = "active";
                        $value['style']                       = "active";
                    }
                    $menu['child'][] = $value;
                }
            }
        }
        $this->assign('__menu__', $menu);
    }

    protected function setMeta($title = '') {
        $this->assign('meta_title', $title);
    }

    protected function getContentMenu() {
        $model = \think\Loader::model('Model');
        $list  = array();
        $map   = array(
            'status' => array('gt', 0)
        );
        $list = $model::where($map)->field("name,id,title,icon,'' as 'style'")->select();

        //判断是否有模型权限
        $models = AuthGroup::getAuthModels(session('user_auth.uid'));
        foreach ($list as $key => $value) {
            if (IS_ROOT || in_array($value['id'], $models)) {
                if ('admin/content/index' == $this->request->path() && input('model_id') == $value['id']) {
                    $value['style'] = "active";
                }
                $value['url']   = "admin/content/index?model_id=" . $value['id'];
                $value['title'] = $value['title'] . "管理";
                $value['icon']  = $value['icon'] ? $value['icon'] : 'file';
                $menu[]         = $value;
            }
        }
        if (!empty($menu)) {
            $this->assign('extend_menu', array('内容管理' => $menu));
        }
    }

    protected function getAddonsMenu() {
        $model = db('Addons');
        $list  = array();
        $map   = array(
            'isinstall' => array('gt', 0),
            'status' => array('gt', 0),
        );
        $list = $model->field("name,id,title,'' as 'style'")->where($map)->select();

        $menu = array();
        foreach ($list as $key => $value) {
            $class = "\\addons\\" . strtolower($value['name']) . "\\controller\\Admin";
            if (is_file(ROOT_PATH .'/addons/' . strtolower($value['name']) . "/controller/Admin.php")) {
                $action       = get_class_methods($class);
                $value['url'] = "admin/addons/execute?mc=" . strtolower($value['name']) . "&ac=" . $action[0];
                $menu[$key]   = $value;
            }
        }
        if (!empty($menu)) {
            $this->assign('extend_menu', array('管理插件' => $menu));
        }
    }

    protected function getArrayParam(){
        $param = $this->request->param();
        return is_array($param['id']) ? array('IN', $param['id']) : $param['id'];
    }
}