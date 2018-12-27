<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/27
 * Time: 21:09
 */

namespace wx;


class MiniApi extends WxApi
{

    protected $appSecret;
    protected $mdasSecret = 'kwTvC3LCjXHT35si1U6m0IQ4IKsLupGi';
    protected $wxhost = array(
        'wxlogin' => 'https://api.weixin.qq.com/sns/jscode2session?',
        'wxaqrcode' => 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=',
        'wxacode' => 'https://api.weixin.qq.com/wxa/getwxacode?access_token=',
    );
    protected $midas = array(
        'balance'=> 'https://api.weixin.qq.com/cgi-bin/midas/getbalance?access_token=',
        'pay'=> 'https://api.weixin.qq.com/cgi-bin/midas/pay?access_token=',
        'cancelpay'=> 'https://api.weixin.qq.com/cgi-bin/midas/cancelpay?access_token=',
        'present'=> 'https://api.weixin.qq.com/cgi-bin/midas/present?access_token=',
    );

    public function __construct($appid,$isDebug){
        parent::__construct($appid);
        if($isDebug){
            $this->midas = array(
                'balance'=> 'https://api.weixin.qq.com/cgi-bin/midas/sandbox/getbalance?access_token=',
                'pay'=> 'https://api.weixin.qq.com/cgi-bin/midas/sandbox/pay?access_token=',
                'cancelpay'=> 'https://api.weixin.qq.com/cgi-bin/midas/sandbox/cancelpay?access_token=',
                'present'=> 'https://api.weixin.qq.com/cgi-bin/midas/sandbox/present?access_token=',
            );
            $this->midas = 'obdjavyWXH1xe3qnr0XNqMNirzCPfZ8n';
        }
    }

    /**
     * 小程序登录
     * @param $code
     * @return mixed
     */
    public function wxLogin($code, $appSecret)
    {
        $url = $this->wxhost['wxlogin'] . 'appid=' . $this->appid . '&secret=' . $appSecret . '&js_code=' . $code . '&grant_type=authorization_code';
        $return = json_decode($this->curl_http($url), true);
        session('wx_session_key',$return['session_key']);
        return json_decode($this->curl_http($url), true);
    }

    /**
     * 获取小程序码A
     * @param $appSecret
     * @return mixed
     */
    public function createWXAQRCode($appSecret,$data,$type)
    {
        $accessToken = $this->getAccessToken($appSecret);
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


// +----------------------------------------------------------------------
// | 米大师虚拟支付——游戏币模式
// +----------------------------------------------------------------------
// | 查询余额 getBalance
// | 游戏扣费 payment
// | 游戏退款 refund
// | 直接赠送 present
// +----------------------------------------------------------------------

    public function getBalance(){

    }

    public function payment(){

    }

    public function refund(){

    }

    public function present(){
        $params = array(
            'openid' => $this->appid,
            'appid' => $this->appid,
            'offer_id' => $this->appid,
            'ts' => (string)time(),
            'zone_id' => $this->appid,
            'pf' => 'android',
            'user_ip' => get_client_ip(),
            'bill_no' => '',
            'present_counts' => 'android',
        );
        $params['sig'] = $this->getSign($params,'/cgi-bin/midas/present');
        $params['access_token'] = $this->getAccessToken($this->appSecret);
        $params['mp_sig'] = $this->getMpSig($params);
    }

    private function getSign($params,$path,$type='sign')
    {
        $secret = $type == 'sign'? $this->mdasSecret: session('wx_session_key');
        $string = '';
        ksort($params);
        foreach ($params as $k => $v) {
            $string .= $k . "=" . $v . "&";
        }
        $string .= '&org_loc='.$path.'&method=POST&secret='.$this->mdasSecret;
        return hash_hmac('sha256', $string, $this->mdasSecret, true);
    }

    private function getMpSig($params){

    }
}