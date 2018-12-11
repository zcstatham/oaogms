<?php

namespace app\http\middleware;

use encrypt\EncryptService;
use think\Request;
use think\Response;

class Check
{
    public function handle(Request $request, \Closure $next)
    {
        $jwt = new EncryptService();
        $checkToken = $jwt->checkToken($request->header('token'));
        if(isset($checkToken['code']) && $checkToken['code'] != '200'){
            return Response::create(json_encode($checkToken));
        }
        return $next($request);
    }
}
