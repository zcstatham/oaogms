<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:38
 */

namespace app\common\model;


use think\facade\Log;

class Mini extends Base
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

    public function getMiniInfoById($key){
        $mid = strcode($key,'decode');
        try{
            $mini = $this->get($mid)->cache(true);
            Log::write($mini,'notice');
            return $mini;
        }catch (\think\Exception $e){
            $ret['msg'] = $e->getMessage();
            return $ret;
        }
    }

    public function getMiniInfoByAppId($key){
        try{
            $mini = $this->where('m_sid',$key)->cache(true);
            Log::write($mini,'notice');
            return $mini;
        }catch (\think\Exception $e){
            $ret['msg'] = $e->getMessage();
            return $ret;
        }
    }
}