<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//Route::get('think', function () {
//    return 'hello,ThinkPHP5!';
//});
//
use think\facade\Route;

Route::domain('api',function() {
    Route::group('checkToken',[
        '/wxpay/notify'    	   => 'api/index/wxpay?type=notify', //订单回调
        '/index/advertisement' => ['api/home/advertisement', ['method' => 'post']], //获取广告
    ])->middleware('Check');
    Route::rule('/login','api/index/login');
});


Route::domain('admin',function() {
    Route::group('checkToken',[
        '/'               => 'admin/index/index',

        'publice/index'   => 'admin/publice/index',
        'publice/own'     => 'admin/publice/own',
        'publice/channel' => 'admin/publice/channel',

        'manager/index'   => 'admin/manager/index',
        'manager/own'     => 'admin/manager/own',
        'manager/channel' => 'admin/manager/channel',
        'manager/account' => 'admin/manager/account',
    ])->middleware('Auth');
    Route::group('checkToken',[
        '/login'   => 'admin/index/login',
        '/logout'     => 'admin/index/logout',
        '/verify' => 'admin/index/verify',
    ]);
})->method('post');

//return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '__domain__'  => array(
//		'api'    => array(
//			//订单支付回调
//            '/'                             => 'api/index/index', //测试
//            '/login'                        => 'api/index/login', //测试
//			'/wxpay/notify'    				=> 'api/index/wxpay?type=notify', //订单回调
//			//首页
//			'/index/advertisement'  		=> ['api/home/advertisement',['method'=>'post']], //获取广告
//		),
//		'admin'      => array(
//            '/'               => 'admin/index/index',
//
//            'publice/index'   => 'admin/publice/index',
//            'publice/own'     => 'admin/publice/own',
//            'publice/channel' => 'admin/publice/channel',
//
//            'manager/index'   => 'admin/manager/index',
//            'manager/own'     => 'admin/manager/own',
//            'manager/channel' => 'admin/manager/channel',
//            'manager/account' => 'admin/manager/account',
//		),
//        'index'      => array(
//            '/'               => 'admin/index/index',
//        )
//	),
//
//];