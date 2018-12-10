<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 20:06
 */
namespace app\admin\model;


class MiniUser extends Base {

    /**
     * 获取小程序用户统计信息
     * @return array('累计用户'，'新增用户'，'渠道用户')
     */
    public function getUserInfo(){
        $sum = $this->cache(true)->count();
        $newSum = $this->cache(true)->where('create_timestamp','>=',date('Y-m-d'))->count();
        $newQSum = $this->cache(true)->where(['create_timestamp',['>=',date('Y-m-d')],'qid' => ['=', 'not null']])->count();
        return [
            'sum'=> $sum,
            'newSum' => $newSum,
            'newQSum' => $newQSum
        ];
    }
}