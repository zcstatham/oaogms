<?php

namespace app\http\middleware;

use app\common\exception\EncryptException;
use app\common\exception\HttpException;
use encrypt\EncryptService;
use think\Request;
use traits\controller\Jump;

class Check
{
    use Jump;

    public function handle(Request $request, \Closure $next)
    {
        if(!$request->isPost()){
            throw new HttpException('2124');
        }
        $jwt = new EncryptService();
        $checkToken = $jwt->checkToken($request->header('Authorization'));
        if(isset($checkToken['code']) && $checkToken['code'] != '200'){
            throw new EncryptException();
        }
        if($checkToken['data']['scopes'] === 'refresh_token'){
            return json($jwt->createToken($checkToken['data']['params']));
        }
        $request->userInfo = $checkToken['data'];
        return $next($request);
    }
}
