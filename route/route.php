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
    '/'               => 'index/index/index',

    'publice/index'   => 'index/publice/index',
    'publice/own'     => 'index/publice/own',
    'publice/channel' => 'index/publice/channel',

    'manager/index'   => 'index/manager/index',
    'manager/own'     => 'index/manager/own',
    'manager/channel' => 'index/manager/channel',
    'manager/account' => 'index/manager/account',

];