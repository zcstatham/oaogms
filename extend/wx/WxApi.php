<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/25
 * Time: 18:51
 */

namespace wx;


use think\facade\Session;
class WxApi
{
    protected $appid;
    protected $access = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&';

    public function __construct($appid)
    {
        $this->appid = $appid;
    }

    /**
     * 获取调用凭据
     * @param $appSecret
     * @return mixed {"access_token": "ACCESS_TOKEN", "expires_in": 7200}
     */
    public function getAccessToken($appSecret)
    {
        $accessToken = session('wxAccessToken');
        if (!$accessToken['access_token'] || time()>$accessToken['time']) {
            $url = $this->access . 'appid=' . $this->appid . '&secret=' . $appSecret;
            $accessToken = json_decode($this->curl_http($url), true);
            \session('access_token',[
                'access_token'=>$accessToken['access_token'],
                'time'=>time() + $accessToken['expires_in'],
            ]);
            $accessToken = $accessToken['access_token'];
        }
        return $accessToken;
    }

    /**
     * Curl
     * @param $url
     * @param string $post
     * @param string $cookie
     * @param bool $returnCookie
     * @return mixed|string
     */
    function curl_http($url, $post = '', $cookie = '', $returnCookie = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }
}