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

//api接口版本路由配置
//Route::rule(':version/apiver/:id','api/:version.Apiver/read');

Route::domain('api',array(
    //production.0 版本路由
    'log/lg'                     => 'api/production.index/login',
    'log/ah'                     => 'api/production.index/authed',
    'log/ad'                     => 'api/production.index/browsead',

    //v2.0 版本路由
    'development/'                     => 'api/development.index/index',
    'development/login'                => 'api/development.index/login',
));

Route::group(['method'=>'post','middleware'=>'Check','domain'=>'api'],array(
    'development/log/visited'   => 'api/development.log/visited',
    'development/log/browsead'   => 'api/development.log/browsead',

    'development/orders/transfers'   => 'api/development.orders/transfers',
    'development/orders/recharge'   => 'api/development.orders/recharge',
    'development/orders/getbalance'   => 'api/development.orders/getbalance',
    'development/orders/pay'   => 'api/development.orders/pay',
    'development/orders/refund'   => 'api/development.orders/refund',
    'development/orders/reward'   => 'api/development.orders/reward',
));


Route::domain('oaogms',array(
    '/'               => 'admin/index/index',
    '/clear'               => 'admin/index/clear',

    'publice/index'   => 'admin/publice/index',
    'publice/own'     => 'admin/publice/own',
    'publice/channel' => 'admin/publice/channel',

    'mini/index'   => 'admin/mini/index',
    'mini/own'     => 'admin/mini/own',
    'mini/channel' => 'admin/mini/channel',

    'system/index' => 'admin/user/index',
    'system/group' => 'admin/group/index',
    'system/auth' => 'admin/group/access',

    '/login'   => 'admin/index/login',
    '/logout'     => 'admin/index/logout',
));

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