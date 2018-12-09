<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\index\controller;


class Manager extends Base
{

    public function index(){
        return $this->own();
    }

    /**
     * 自有小程序列表
     * @return string
     */
    public function own(){
        return Common/$this->own();
    }

    /**
     * 渠道小程序列表
     * @return string
     */
    public function channel(){
        return 'channel';
    }

    /**
     * 平台账户列表
     * @return string
     */
    public function account(){
        return 'account';
    }

    /**
     * 新增小程序
     * @param $data
     * @return mixed
     */
    private function addMini($data){
        return $data;
    }

    /**
     * 修改小程序
     * @param $mid
     * @return mixed
     */
    private function editMini($mid){
        return $mid;
    }

    /**
     * 绑定小程序
     * @param $mid
     * @return mixed
     */
    private function bindMini($mid){
        return $mid;
    }

    /**
     * 删除小程序
     * @param $mid
     * @return mixed
     */
    private function delMini($mid){
        return $mid;
    }

    /**
     * 查询小程序
     * @param $mid
     * @return mixed
     */
    private function getMini($mid){
        return $mid;
    }

    /**
     * 新增账户
     * @param $data
     * @return mixed
     */
    private function addAccount($data){
        return $data;
    }

    /**
     * 修改账户
     * @param $mid
     * @return mixed
     */
    private function editAccount($mid){
        return $mid;
    }

    /**
     * 删除账户
     * @param $mid
     * @return mixed
     */
    private function delAccount($mid){
        return $mid;
    }

    /**
     * 查询账户
     * @param $mid
     * @return mixed
     */
    private function getAccount($mid){
        return $mid;
    }
}