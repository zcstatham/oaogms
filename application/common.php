<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login()
{
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null)
{
    $uid = is_null($uid) ? is_login() : $uid;
    return $uid && (intval($uid) === config('user_administrator'));
}

/**
 * 加密、解密字符串
 * ENCODE为加密，DECODE为解密 * 加密就是把字符串的每个字符进行^运算，生成新字符串再base64一下返回。
 * 用来进行^运算的字符串通过MD5一些全局变量再substr获得。
 * 这里注意，^运算必须是2个长度相同的字符串才不会产生掉串，
 * 例如：'asd'^'123' == 'PAW',但是'asd'^'123456'还是等于'PAW',多余的字符掉了，
 * 并且不知道传入的字符串到底是多长，因此生成^运算的字符串也不知道要生成多长，
 * 这里用循环的方式进行处理，即^运算的字符串可以是任意长度，然后要加密的字符串用第一个字符与^运算的字符串的第一个字符进行与运算，
 * 以此类推，当^运算的字符长度不够时就循环使用,上边的for循环里边的取%运算就是这个道理。
 *
 * @global string $db_hash
 * @global array $pwServer
 * @param $string
 * @param $action
 * @return string
 */
function strcode($string, $action = 'encode') {
    $action != 'encode' && $string = base64_decode($string);
    $code = '';
    $key = substr(md5(config('app.siteinfo.mini_salt')), 8, 18);
    $keyLen = strlen($key);
    $strLen = strlen($string);
    for ($i = 0; $i < $strLen; $i++) {
        $k = $i % $keyLen;
        $code .= $string[$i] ^ $key[$k];
    }
    return ($action != 'decode' ? base64_encode($code) : $code);
}
