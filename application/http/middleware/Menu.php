<?php

namespace app\http\middleware;

use think\Container;
use think\Request;

class Menu
{
    public function handle(Request $request, \Closure $next)
    {
        $hover_url  = $request->module() . '/' . $request->controller();
        $controller = strtolower($request->module() . '/' . $request->controller() . '/' . $request->action());
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
        if (!$Auth->check($rule, session('user_auth.uid'), $type, $mode)) {
            return false;
        }
        return true;
    }
}
