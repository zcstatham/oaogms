<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/27
 * Time: 21:18
 */

namespace wx;


class WxApp extends wxApi
{

    protected $appSecret;

    protected $wxhost = array(
        'wxpay' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
        'wxacodeunlimit' => 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=',
    );
    /**
     * 微信支付
     * @param $wxData
     * @param $ownData
     * @return array
     */
    public function wxpay($wxInfo, $orderInfo)
    {
        $unifiedorder = $this->unifiedorder($wxInfo, $orderInfo);
        $parameters = array(
            'appId' => $this->appid,
            'timeStamp' => (string)time(),
            'nonceStr' => $this->getNonceStr(),
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'],//数据包
            'signType' => 'MD5'//签名方式
        );
        $parameters['paySign'] = $this->getSign($parameters);
        return $parameters;
    }

    /**
     * 获取小程序码A
     * @param $appSecret
     * @return mixed
     */
    public function createWXAQRCode($appSecret,$data,$type)
    {
        $accessToken = session('wxAccessToken');
        if (!$accessToken['access_token'] || time()>$accessToken['time']) {
            $accessToken = $this->getAccessToken($appSecret);
            \session('access_token',[
                'access_token'=>$accessToken['access_token'],
                'time'=>time() + $accessToken['expires_in'],
            ]);
            $accessToken = $accessToken['access_token'];
        }
        switch ($type){
            case 'a':
                $url = $this->wxhost['wxacode'] . $accessToken;
                break;
            case 'b':
                $url = $this->wxhost['wxacodeunlimit'] . $accessToken;
                $path = $data['path'];
                $pos = stripos($path,'?');
                $data['scene'] = substr($path,$pos+1);
                $data['path'] = substr($path,0,$pos);
                break;
            case 'c':
                $url = $this->wxhost['wxaqrcode'] . $accessToken;
                break;
        }
        return $this->curl_http($url, $data);
    }

    /**
     * 获取调用凭据
     * @param $appSecret
     * @return mixed {"access_token": "ACCESS_TOKEN", "expires_in": 7200}
     */
    public function getAccessToken($appSecret)
    {
        $url = $this->wxhost['access'] . 'appid=' . $this->appid . '&secret=' . $appSecret;
        return json_decode($this->curl_http($url), true);
    }

    /**
     * 生成签名，下单
     * @param $wxInfo
     * @param $orderInfo
     * @return mixed
     */
    private function unifiedorder($wxInfo, $orderInfo)
    {
        $parameters = array(
            'appid' => $this->appid,//小程序ID
            'mch_id' => $wxInfo['mch_id'],//商户号
            'nonce_str' => $this->getNonceStr(),//随机字符串
            'body' => '测试',//商品描述
            'sign' => '签名',//商品描述
            'out_trade_no' => $orderInfo['orderId'],//商户订单号
            'total_fee' => floatval($orderInfo['money'] * 100),//总金额 单位 分
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//终端IP
            'notify_url' => $orderInfo['notify_url'],//支付回调
            'trade_type' => 'JSAPI',//交易类型);
            'openid' => $wxInfo['openid'],//用户id
        );
        // 统一下单签名
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = arrayToXml($parameters, 'xml');
        return xmlToArray(curl_http($this->wxhost['wxpay'], $xmlData));
    }

    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    private function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 签名
     * @param $parameters
     * @return string
     */
    private function getSign($parameters)
    {
        $string = '';
        ksort($parameters);
        foreach ($parameters as $k => $v) {
            $string .= $k . "=" . $v . "&";
        }
        return strtoupper(md5($string . '$key=' . $this->appSecret));
    }

    public function wx_auth_sign($signature, $rawData, $encryptedData, $iv)
    {
        $session_key = session('wx_session_key');
        $signature2 = sha1($rawData . $session_key);
        if ($signature != $signature2) {
            return false;
        }
        $wxBizDatCrypt = new \WXBizDataCrypt($this->appid, $session_key);
        $errCode = $wxBizDatCrypt->decryptData($encryptedData, $iv, $data);
        $data = json_decode($data);
        if ($errCode == 0) {
            return $data;
        } else {
            return $errCode;
        }
    }
}