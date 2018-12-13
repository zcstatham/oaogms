<?php

namespace app\http\middleware;

use encrypt\EncryptService;
use think\Request;
use think\Response;

class Check
{
    public function handle(Request $request, \Closure $next)
    {
        if(!$request->isPost()){
            json_error_exception('1001');
        }
        $jwt = new EncryptService();
        $checkToken = $jwt->checkToken($request->header('token'));
        if(isset($checkToken['code']) && $checkToken['code'] != '200'){
            return Response::create(json_encode($checkToken),'json');
        }
        return $next($request);
    }
}
