<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 19:49
 */

namespace wx;

require 'common.php';
class wxPay
{
    protected $appid;
    protected $mch_id;
    protected $key;
    protected $openid;
    protected $unifiedorder = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public function __construct($appid, $openid, $mch_id, $key)
    {
        $this->appid = $appid;
        $this->openid = $openid;
        $this->mch_id = $mch_id;
        $this->key = $key;
    }

    public function wxpay($notify_url, $orderId, $money)
    {
        $unifiedorder = $this->unifiedorder($notify_url, $orderId, $money);
        $parameters = array(
            'appId' => $this->appid,
            'timeStamp' => (string) time(),
            'nonceStr' => $this->getNonceStr(),
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'],//数据包
            'signType' => 'MD5'//签名方式
        );
        $parameters['paySign'] = $this->getSign($parameters);
        return $parameters;
    }

    private function unifiedorder($notify_url, $orderId, $money)
    {
        $parameters = array(
            'appid' => $this->appid,//小程序ID
            'mch_id' => $this->mch_id,//商户号
            'nonce_str' => $this->getNonceStr(),//随机字符串
            'body' => '测试',//商品描述
            'sign' => '签名',//商品描述
            'out_trade_no' => $orderId,//商户订单号
            'total_fee' => floatval($money * 100),//总金额 单位 分
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//终端IP
            'notify_url' => $notify_url,//支付回调
            'trade_type' => 'JSAPI',//交易类型);
            'openid' => $this->openid,//用户id
        );
        // 统一下单签名
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = arrayToXml($parameters,'xml');
        return xmlToArray(curl_http($this->unifiedorder,$xmlData));
    }

    //产生随机字符串，不长于32位
    private function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getSign($parameters)
    {
        $string = '';
        ksort($parameters);
        foreach ($parameters as $k => $v) {
            $string .= $k . "=" . $v . "&";
        }
        return strtoupper(md5($string . '$key=' . $this->key));
    }
}