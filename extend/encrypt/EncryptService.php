<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 19:19
 */

namespace encrypt;

use \Firebase\JWT\JWT;
use think\Exception; //导入JWT

class EncryptService
{

    /**
     * 头部 公共参数
     * @param array $header 头部参数数组
     * @param string $alg 声明签名算法为SHA256
     * @return string $typ 声明类型为jwt
     */
    public static $TokenKey = 'oAofD99PY76cs8Gvt';

    /**
     * 创建 token
     * @param array $data 必填 自定义参数数组
     * @param integer $exp_time 必填 token过期时间 单位:秒 例子：7200=2小时
     * @param string $scopes 选填 token标识，请求接口的token
     * @return string
     */
    /**
     * 创建 token
     * @param string $data 必填 自定义参数数组
     * @param int $exp_time 必填 token过期时间
     * @param string $scopes 选填 token标识，请求接口的token
     * @return string
     */
    public static function createToken($data = "", $exp_time = 0, $scopes = "")
    {
        //JWT标准规定的声明，但不是必须填写的；
        //iss: jwt签发者
        //sub: jwt所面向的用户
        //aud: 接收jwt的一方
        //exp: jwt的过期时间，过期时间必须要大于签发时间
        //nbf: 定义在什么时间之前，某个时间点后才能访问
        //iat: jwt的签发时间
        //jti: jwt的唯一身份标识，主要用来作为一次性token。
        //公用信息
        try {
            $key = self::$TokenKey;
            $time = time(); //当前时间
            $token['iss'] = 'oaogms'; //签发者 可选
            $token['aud'] = 'oaogms'; //接收该JWT的一方，可选
            $token['iat'] = $time; //签发时间
            $token['nbf'] = $time; //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            if ($scopes) {
                $token['scopes'] = $scopes; //token标识，请求接口的token
            }
            if (!$exp_time) {
                $exp_time = 7200;//默认=2小时过期
            }
            $token['exp'] = $time + $exp_time; //token过期时间,这里设置2个小时
            if ($data) {
                $token['data'] = $data; //自定义参数
            }
            $token = [
                'iss' => 'http://www.fn321.com|Fneducms', //签发者 可选
                'aud' => 'http://www.fn321.com|Fneducms', //接收该JWT的一方，可选
                'iat' => $time, //签发时间
                'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
                'scopes' => $scopes, //token标识，请求接口的token
                'exp' => $time + $exp_time, //token过期时间,这里设置2个小时
                'params' => $data
            ];

            $json = JWT::encode($token, $key);
            return $json; //返回给客户端token信息

        } catch (\Firebase\JWT\ExpiredException $e) {  //签名不正确
            $returndata['code'] = "104";//101=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        } catch (\UnexpectedValueException $e) {  //其他错误
            $returndata['code'] = "199";//199=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        } catch (Exception $e) {  //其他错误
            $returndata['code'] = "199";//199=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        }
    }

    /**
     * 验证token是否有效,默认验证exp,nbf,iat时间
     * @param $jwt
     * @return mixed
     */
    public static function checkToken($jwt)
    {
        $key = self::$TokenKey;

        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($jwt, $key, ['HS256']); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;

            $returndata['code'] = "200";//200=成功
            $returndata['msg'] = "成功";//
            $returndata['data'] = $arr;//返回的数据
            return $returndata; //返回信息

        } catch (\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            $returndata['code'] = "101";//101=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        } catch (\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            $returndata['code'] = "102";//102=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        } catch (\Firebase\JWT\ExpiredException $e) {  // token过期
            $returndata['code'] = "103";//103=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        } catch (\UnexpectedValueException $e) {  //其他错误
            $returndata['code'] = "199";//199=签名不正确
            $returndata['msg'] = $e->getMessage();
            $returndata['data'] = "";//返回的数据
            return $returndata; //返回信息
        }
        //Firebase定义了多个 throw new，我们可以捕获多个catch来定义问题，catch加入自己的业务，比如token过期可以用当前Token刷新一个新Token
    }

    /**
     * 接口首页 没有内容
     * @url /api/v1/index
     * @method POST
     */
    public function index()
    {
        //url:http://www.cms.com/api/v1/index

        //自定义信息，不要定义敏感信息
        $data['userid'] = 21;//用户ID
        $data['username'] = "李10小龙";//用户ID
        $exp_time = 7200; //token过期时间,这里设置2个小时
        $scopes = 'role_access'; //token标识，请求接口的token

        //生成签名
        $json = action('createToken', ['data' => $data, 'exp_time' => $exp_time, 'scopes' => $scopes]);
        //echo $json."<br>"; //返回给客户端token信息
        //验证签名
        $checkToken = action('checkToken', ['jwt' => $json]);
        Header("HTTP/1.1 201 Created");
        echo $checkToken; //返回给客户端token信息

    }

    /**
     * 获取用户登录信息
     * @url /api/v1.index/login
     * @method POST
     * @param integer $page 页数
     * @param integer $limit 每页个数
     * @return integer $code 状态码
     * @return string $msg 返回消息
     */
    public function login()
    {

        //登录思路：客户端通过用户名密码登录以后，服务端返回给客户端两个token：access_token和refresh_token。
        //access_token：请求接口的token
        //refresh_token：刷新access_token
        //举个例子：比如access_token设置2个小时过期，refresh_token设置7天过期，2小时候后，access_token过期，但是refresh_token还在7天以内，那么客户端通过refresh_token来服务端刷新，服务端重新生成一个access_token；
        //如果refresh_token也超过了7天，那么客户端需要重新登录获取access_token和refresh_token。
        //为了区分两个token，我们在载荷（payload)加一个字段 scopes ：作用域。
        //access_token中设置：scopes:role_access
        //refresh_token中设置：scopes:role_refresh

        //自定义信息，不要定义敏感信息
        $data['userid'] = 21;//用户ID
        $data['username'] = "李小龙";//用户ID

        //请求接口的token
        $exp_time1 = 7200; //token过期时间,这里设置2个小时
        $scopes1 = 'role_access'; //token标识，请求接口的token
        $access_token = action('createToken', ['data' => $data, 'exp_time' => $exp_time1, 'scopes' => $scopes1]);

        //刷新refresh_token
        $exp_time2 = 86400 * 30; //refresh_token过期时间,这里设置30天
        $scopes2 = 'role_refresh'; //token标识，刷新access_token
        $refresh_token = action('createToken', ['data' => $data, 'exp_time' => $exp_time2, 'scopes' => $scopes2]);

//					//公用信息
//					$token = [
//					        	'iss' => 'http://www.helloweba.net', //签发者 可选
//					           	'aud' => 'http://www.helloweba.net', //接收该JWT的一方，可选
//					            'iat' => $time, //签发时间
//					            'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
//					            'data' => $data
//			        ];
//			    //请求接口的token 用户名登录验证通过时生成的
//							$access_token = $token; // access_token
//							$access_token['scopes'] = 'role_access'; //token标识，请求接口的token
//							$access_token['exp'] = $time+7200; //access_token过期时间,这里设置2个小时
//			    //刷新access_token
//							$refresh_token = $token; //refresh_token
//							$refresh_token['scopes'] = 'role_refresh'; //token标识，刷新access_token
//							$refresh_token['exp'] = $time+(86400 * 30); //refresh_token过期时间,这里设置30天

        $jsonList = [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => 'bearer' //token_type：表示令牌类型，该值大小写不敏感，这里用bearer
        ];
        Header("HTTP/1.1 201 Created");
        echo json_encode($jsonList); //返回给客户端token信息
    }


}
