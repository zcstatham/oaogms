<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/18
 * Time: 21:30
 */

namespace app\admin\controller;


class MiniLog extends Base
{
    public function index(){
        $data = array(
            '__Menu__'=> $this->setMenu($list),
        );
    }

    protected function setMenu($list)
    {
        $menu       = array(
            'own'  => array(),
            'channel' => array(),
        );
        if (getUserType() != 1) {
            $ownlist = model('Mini')->where('sid',session('user_auth.sid'))->all();
            $channellist = model('Mini')->where('sid',1)->all();
        } else {
            $ownlist = model('Mini')->where('sid',1)->all();
            $channellist = model('Mini')->where('sid','<>',1)->all();
        }
        foreach ($ownlist as $key => $value) {
            //此处用来做权限判断
            if (IS_ROOT || $this->checkRule($value['url'], 2, null)) {
                if ($controller == $value['url']) {
                    $value['style'] = "active";
                }
                $menu['main'][$value['nid']] = $value;
            }
        }
        foreach ($channellist as $key => $value) {
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
}