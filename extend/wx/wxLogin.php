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
    public function __construct($appid,$appSecret,$openid, $mch_id, $key){
        $this->appid = $appid;
        $this->appSecret = $appSecret;
        $this->mch_id = $mch_id;
        $this->key = $key;
    }

    public function wxLogin($code){
        $url = $this->wxhost.'jscode2session?appid'.$this->appid.'&secret='.$this->appSecret.'&js_code='.$code.'&grant_type=authorization_code';
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
}