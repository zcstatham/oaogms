<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\index\controller;


class Publice extends Base
{
    public function index(){
        return $this->own(1);
    }

    /**
     * 自有小程序数据
     * @return string
     */
    public function own($id){
        return 'own'.$id;
    }

    /**
     * 渠道小程序数据
     * @return string
     */
    public function channel($id){
        return 'channel'.$id;
    }
}