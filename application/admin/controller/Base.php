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
        'Auth' 	=> ['except' => ['login','logout'] ],
    ];

    protected function setMeta($title = '') {
        $this->assign('meta_title', $title);
    }

//    protected function getContentMenu() {
//        $model = \think\Loader::model('Model');
//        $list  = array();
//        $map   = array(
//            'status' => array('gt', 0)
//        );
//        $list = $model::where($map)->field("name,id,title,icon,'' as 'style'")->select();
//
//        //判断是否有模型权限
//        $models = AuthGroup::getAuthModels(session('user_auth.sid'));
//        foreach ($list as $key => $value) {
//            if (IS_ROOT || in_array($value['id'], $models)) {
//                if ('admin/content/index' == $this->request->path() && input('model_id') == $value['id']) {
//                    $value['style'] = "active";
//                }
//                $value['url']   = "admin/content/index?model_id=" . $value['id'];
//                $value['title'] = $value['title'] . "管理";
//                $value['icon']  = $value['icon'] ? $value['icon'] : 'file';
//                $menu[]         = $value;
//            }
//        }
//        if (!empty($menu)) {
//            $this->assign('extend_menu', array('内容管理' => $menu));
//        }
//    }

    protected function getArrayParam(){
        $param = $this->request->param();
        return is_array($param['id']) ? array('IN', $param['id']) : $param['id'];
    }
}