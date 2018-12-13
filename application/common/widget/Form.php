<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/13
 * Time: 19:31
 */

namespace app\common\widget;


use think\Container;

class Form {

    /**
     * 视图类实例
     * @var \think\View
     */
    protected $view;

    public function __construct()
    {
        $this->app = Container::get('app');
        $this->view = $this->app['view'];
    }

    public function show($field, $info) {
        $type = isset($field['type']) ? $field['type'] : 'text';
        //类型合并
        if (in_array($type, array('string'))) {
            $type = 'text';
        }
        if (in_array($type, array('picture'))) {
            $type = 'image';
        }
        $data = array(
            'type'   => $type,
            'field'  => isset($field['name']) ? $field['name'] : '',
            'value'  => isset($info[$field['name']]) ? $info[$field['name']] : (isset($field['value']) ? $field['value'] : ''),
            'size'   => isset($field['size']) ? $field['size'] : 12,
            'option' => isset($field['option']) ? $field['option'] : '',
        );
        $no_tem = array('readonly', 'text', 'password','checkbox', 'textarea', 'select', 'bind', 'checkbox', 'radio', 'num', 'bool', 'decimal');
        $type   = !in_array($type, $no_tem) ? $type : 'show';
        $this->view->assign($data);
        return $this->view->fetch('common@form/' . $type);
    }
    public function showConfig($field, $info) {
        $type = isset($field['type']) ? $field['type'].'_Config' : 'text';
        //类型合并
        if (in_array($type, array('string'))) {
            $type = 'text';
        }
        if (in_array($type, array('picture'))) {
            $type = 'image';
        }

        $data = array(
            'type'   => $type,
            'field'  => isset($field['name']) ? $field['name'] : '',
            'value'  => isset($info[$field['name']]) ? $info[$field['name']] : (isset($field['value']) ? $field['value'] : ''),
            'size'   => isset($field['size']) ? $field['size'] : 12,
            'option' => isset($field['option']) ? $field['option'] : '',
        );
        $no_tem = array('readonly', 'text', 'password','checkbox', 'textarea', 'select', 'bind', 'checkbox', 'radio', 'num', 'bool', 'decimal');
        $type   = !in_array($type, $no_tem) ? $type : 'show';
        $this->view->assign($data);
        return $this->view->fetch('common@form/' . $type);
    }
}