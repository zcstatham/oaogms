<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 21:04
 */

namespace app\api\controller;

use think\facade\Log;
use app\common\exception\HttpException;

class Index extends Base
{

    function index()
    {
        return 'hello,gun ni ma dan';
    }

    /**
     * 小程序用户登录 >> 换取token;
     */
    public function login(){
        $data = model('User')->login($this->params['aid'],$this->params['mid'],$this->params['code']);
        if($data !== false){
            $token = $this->createToken($data['data']);
            Log::record(['response_data'=>$data['info']]);
            return json($data['info'])->header([
                'Cache-control' => 'no-cache,must-revalidate',
                'Authorization'=>json_encode($token)
            ]);
        }
        throw new HttpException();
    }

    public function refresh(){
        $token = action('\encrypt\EncryptService\createToken',['data' => $data, 'exp_time' => 7200, 'scopes' => 'access_token']);
        return json($token);
    }

    public function createToken($data)
    {
        $jwt = new \encrypt\EncryptService();
        $exp_time = 7200;
        $scopes = 'access_token';
        $access_token = $jwt->createToken($data, $exp_time, $scopes);

        $exp_time = 86400 * 7;
        $scopes = 'refresh_token';
        $refresh_token = $jwt->createToken($data, $exp_time, $scopes);

        return array(
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => 'bearer'
        );
    }
}