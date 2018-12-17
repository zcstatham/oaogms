<?php

namespace app\http\middleware;

use think\Container;
use think\Request;
use traits\controller\Jump;

class Auth
{
    use Jump;

    private $url_path;

    public function handle(Request $request, \Closure $next)
    {
        $this->url_path = strtolower($request->module() . '/' . $request->controller() . '/' . $request->action());
        if (!in_array(strtolower($request->url()), array('admin/index/login', 'admin/index/logout', 'admin/index/verify'))) {
            // 是否是超级管理员
            define('IS_ROOT', is_administrator());
            // 检测系统权限
            if (!IS_ROOT) {
                $access = $this->accessControl($request);
                if (false === $access) {
                    $this->error('403:禁止访问','/login');
                } elseif (null === $access) {
                    $dynamic = $this->checkDynamic(); //检测分类栏目有关的各项动态权限
                    if ($dynamic === null) {
                        //检测访问权限
                        if (!$this->checkRule($this->url_path, '1,2')) {
                            $this->error('未授权访问!','/login');
                        } else {
                            // 检测分类及内容有关的各项动态权限
                            $dynamic = $this->checkDynamic();
                            if (false === $dynamic) {
                                $this->error('未授权访问!','/login');
                            }
                        }
                    } elseif ($dynamic === false) {
                        $this->error('未授权访问!','/login');
                    }
                }
            }
            $this->setMenu($request);
        }
        return $next($request);
    }

    protected function checkDynamic() {
        if (IS_ROOT) {
            return true; //管理员允许访问任何页面
        }
        return null; //不明,需checkRule
    }

    final protected function accessControl(Request $request) {
        $allow = config('siteinfo.allow_visit');
        $deny = config('siteinfo.deny_visit');
        $check = strtolower($request->controller() . '/' . $request->action());
        if (!empty($deny) && in_array_case($check, $deny)) {
            return false; //非超管禁止访问deny中的方法
        }
        if (!empty($allow) && in_array_case($check, $allow)) {
            return true;
        }
        return null; //需要检测节点权限
    }

    protected function setMenu(Request $request)
    {
        $hover_url  = $request->module() . '/' . $request->controller();
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
            if (IS_ROOT || $this->checkRule($value['url'], 2, null)) {
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
        if ($pid) {
            $map['pid']  = $pid;
            $map['hide'] = 0;
            $map['type'] = 'admin';
            $row = db('menu')->field("nid,title,url,icon,`group`,pid,'' as style")->where($map)->order('sort asc')->select();
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
        Container::get('app')['view']->assign('__menu__', $menu);
    }

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function checkRule($rule, $type = 1, $mode = 'url') {
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \author\Auth();
        }
        if (!$Auth->check($rule, session('user_auth.sid'), $type, $mode)) {
            return false;
        }
        return true;
    }
}
