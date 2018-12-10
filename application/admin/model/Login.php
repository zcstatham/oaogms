<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:37
 */

namespace app\index\model;


class Login extends Base
{
    /**
     * 获取访问次数
     * @param $day
     * @return string
     */
    private function visitedCount($day)
    {
        return 'visitedCount';
    }

    /**
     * 获取访问人次
     * @param $day
     * @return string
     */
    private function visitedUserCount($day)
    {
        return 'visitedUserCount';
    }

    /**
     * 获取渠道访问人次
     * @param $day
     * @return string
     */
    private function channelVisitedUserCount($day)
    {
        return 'visitedUserCount';
    }

}