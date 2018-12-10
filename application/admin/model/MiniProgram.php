<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:38
 */

namespace app\admin\model;


class MiniProgram extends Base
{
    /**
     * 自有小程序数据
     * @return string
     */
    public function own(){
        return own();
    }

    /**
     * 渠道小程序数据
     * @return string
     */
    public function channel(){
        return channel();
    }
}