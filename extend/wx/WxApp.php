<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/27
 * Time: 21:18
 */

namespace wx;


class WxApp extends WxApi
{

    protected $mchid = '1507521521';
    protected $appSecret = 'oaottt131419oaottt131419oaottt13';

    protected $wxhost = array(
        'wxpay' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
        'transfers' => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers',
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
        $params = array(
            'appId' => $this->appid,
            'timeStamp' => (string)time(),
            'nonceStr' => $this->getNonceStr(),
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'],//数据包
            'signType' => 'MD5'//签名方式
        );
        $params['paySign'] = $this->getSign($params);
        return $params;
    }

    public function transfers($order){
        $params = array(
            'mch_appid' => $this->appid,//小程序ID
            'mchid' => $this->mchid,//商户号
            'nonce_str' => $this->getNonceStr(),//随机字符串
            'partner_trade_no' => $order['order_sn'],//商户订单号
            'openid' => $order['openid'],//待提现用户openid
            'check_name' => 'FORCE_CHECK',
            're_user_name' => '赵铖',
            'amount' => floatval($order['money'] * 1),//总金额 单位 分
            'desc' => $order['desc'],
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//终端IP
        );
        $params['sign'] = $this->getSign($params);
        $xmlData = $this->arrayToXml($params, 'xml');
        return $this->xmlToArray($this->curl_http($this->wxhost['transfers'], $xmlData,'xml',true));
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
        $params = array(
            'appid' => $this->appid,//小程序ID
            'mch_id' => $this->mchid,//商户号
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
        $params['sign'] = $this->getSign($params);
        $xmlData = $this->arrayToXml($params, 'xml');
        return $this->xmlToArray($this->curl_http($this->wxhost['wxpay'], $xmlData));
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
     * @param $params
     * @return string
     */
    private function getSign($params)
    {
        $string = '';
        ksort($params);
        foreach ($params as $k => $v) {
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