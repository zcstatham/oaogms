<?php

namespace app\http\middleware;

class Auth
{
    public function handle($request, \Closure $next)
    {
        if (!in_array(strtolower($request->url), array('admin/index/login', 'admin/index/logout', 'admin/index/verify'))) {
            // 是否是超级管理员
            define('IS_ROOT', is_administrator());
            // 检测系统权限
            if (!IS_ROOT) {
                $access = $this->accessControl();
                if (false === $access) {
                    $this->error('403:禁止访问');
                } elseif (null === $access) {
                    $dynamic = $this->checkDynamic(); //检测分类栏目有关的各项动态权限
                    if ($dynamic === null) {
                        //检测访问权限
                        if (!$this->checkRule($this->url_path, array('in', '1,2'))) {
                            $this->error('未授权访问!');
                        } else {
                            // 检测分类及内容有关的各项动态权限
                            $dynamic = $this->checkDynamic();
                            if (false === $dynamic) {
                                $this->error('未授权访问!');
                            }
                        }
                    } elseif ($dynamic === false) {
                        $this->error('未授权访问!');
                    }
                }
            }
            //菜单设置
//            $this->setMenu();
        }
    }

    protected function checkDynamic() {
        if (IS_ROOT) {
            return true; //管理员允许访问任何页面
        }
        return null; //不明,需checkRule
    }

    final protected function accessControl() {
        $allow = config('app.siteinfo.allow_visit');
        $deny = config('app.siteinfo.deny_visit');
//        $check = strtolower($request->controller() . '/' . $this->request->action());
        if (!empty($deny) && in_array_case($check, $deny)) {
            return false; //非超管禁止访问deny中的方法
        }
        if (!empty($allow) && in_array_case($check, $allow)) {
            return true;
        }
        return null; //需要检测节点权限
    }
}
