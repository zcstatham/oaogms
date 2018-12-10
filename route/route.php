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
//Route::get('hello/:name', 'index/hello');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '/'               => 'admin/index/index',

    'publice/index'   => 'admin/publice/index',
    'publice/own'     => 'admin/publice/own',
    'publice/channel' => 'admin/publice/channel',

    'manager/index'   => 'admin/manager/index',
    'manager/own'     => 'admin/manager/own',
    'manager/channel' => 'admin/manager/channel',
    'manager/account' => 'admin/manager/account',

];