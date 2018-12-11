<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:37
 */

namespace app\common\model;


class MiniActionLog extends Base
{
    /**
     * 获取渠道导出人次
     * @param $day
     * @return string
     */
    private function channelExportedUserCount($day)
    {
        return 'visitedUserCount';
    }

}