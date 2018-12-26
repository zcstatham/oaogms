<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 17:40
 */

namespace wx;

class wxLogin
{
    protected $appid;
    protected $appSecret;
    protected $wxhost = 'https://api.weixin.qq.com/sns/';

    public function __construct($appid,$appSecret){
        $this->appid = $appid;
        $this->appSecret = $appSecret;
    }

    public function wxLogin($code){
        $url = $this->wxhost.'jscode2session?appid='.$this->appid.'&secret='.$this->appSecret.'&js_code='.$code.'&grant_type=authorization_code';
        return json_decode($this->curl_http($url),true);
    }

    public function wx_auth_sign($signature,$rawData,$encryptedData,$iv){
        $session_key = session('wx_session_key');
        $signature2 = sha1($rawData . $session_key);
        if ($signature != $signature2) {
            return false;
        }
        $wxBizDatCrypt = new \WXBizDataCrypt($this->appid,$session_key);
        $errCode = $wxBizDatCrypt->decryptData($encryptedData, $iv, $data);
        $data = json_decode($data);
        if ($errCode == 0) {
            return $data;
        } else {
            return $errCode;
        }
    }

    function curl_http($url,$post='',$cookie='', $returnCookie=false){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }
}