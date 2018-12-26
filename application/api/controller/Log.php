<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/26
 * Time: 21:40
 */

namespace app\api\controller;


use think\Controller;

class Log extends Controller
{

    protected $beforeActionList = [
        'checkDate'
    ];

    protected $param;

    /**
     * 登录日志
     * 写入登录日志
     */
    public function login()
    {

    }

    /**
     * 授权日志
     */
    public function authed()
    {

    }

    //看广告
    public function browsead()
    {

    }

    public function save($data)
    {
        $data['action_ip'] = get_client_ip();
        return model('MiniLog')->save($data);
    }

    protected function checkDate()
    {
        $this->param = $this->request->param();
        if(isset($this->param['aid'])){
            $this->param['aid'] = decodeN($this->param['aid']);
        }else {
            $this->param['aid'] = 0;
        }
        if (isset($this->param['mid'])&& !empty($this->param['mid'])) {
            $this->param['mid'] = decodeN($this->param['mid']);
        }else {
            json_error_exception('1003');
        }
    }
}