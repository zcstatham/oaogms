<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/27
 * Time: 21:09
 */

namespace wx;

use think\Request;
class MiniApi extends WxApi
{

    protected $appSecret;
    protected $sessionkey;
    protected $mdasSecret = 'kwTvC3LCjXHT35si1U6m0IQ4IKsLupGi';
    protected $wxhost = array(
        'wxlogin' => 'https://api.weixin.qq.com/sns/jscode2session?',
        'wxaqrcode' => 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=',
        'wxacode' => 'https://api.weixin.qq.com/wxa/getwxacode?access_token=',
        'checksession' => 'https://api.weixin.qq.com/wxa/checksession?access_token=',
    );
    protected $midasUrl = 'https://api.weixin.qq.com/cgi-bin/midas/%action%?access_token=';

    public function __construct($appid,$isDebug){
        parent::__construct($appid);
        if($isDebug){
            $this->midasUrl = 'https://api.weixin.qq.com/cgi-bin/midas/sandbox/%action%?access_token=';
            $this->midas = 'obdjavyWXH1xe3qnr0XNqMNirzCPfZ8n';
        }
        $this->sessionkey = decrypt(Request->param('userInfo')['sessionkey'],config('siteinfo.mini_salt'));
        $checksession = $this->checkSessionKey();
        if($checksession['errcode'] != 0){
            return false;
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

    private function checkSessionKey(){
        $url = $this->wxhost['checksession'] .$this->getAccessToken($appSecret). 'signature=' . hash_hmac('sha256', '', $this->sessionkey, true) . '&openid=' . Request->param('userInfo')['openid'] . '&sig_method=hmac_sha256';
        return json_decode($this->curl_http($url), true);
    }


// +----------------------------------------------------------------------
// | 米大师虚拟支付——游戏币模式
// +----------------------------------------------------------------------
// | 统一接口 mdasPayment
// | 操作类型 type
// | 操作参数 data
// | 返回值   json
// +----------------------------------------------------------------------
    public function mdasPayment($type,$data){
        $params = array(
            'openid' => $data['appid'],
            'appid' => $this->appid,
            'offer_id' => $data['offerid'],
            'ts' => (string)time(),
            'zone_id' => $data['zoneid'],
            'pf' => 'android',
            'user_ip' => get_client_ip(),
            'bill_no' => $data['billno'],
        );
        switch ($type) {
            case 'present':
                $params['present_counts'] = $data['item'];
                break;
            case 'pay':
                $params['amt'] = $data['item'];
                break;
            case 'getbalance':
                unset($params['bill_no']);
                break;
            case 'cancelpay':
                $params['pay_item'] = $data['item'];//否
                break;
        }
        $url = strtr($midasUrl,'%action%',$type);
        $uri = strtr('//cgi-bin//midas//action','action',$type);
        $params['sig'] = $this->getSign($params,$uri);
        $params['access_token'] = $this->getAccessToken($this->appSecret);
        $params['mp_sig'] = $this->getMpSig($params,$uri,'mp_sig');
        return json_decode($this->curl_http($url, true);
    }

    private function getSign($params,$path,$type='sig')
    {
        $secret = $type == 'sig'? $this->mdasSecret:$this->sessionkey;
        $string = '';
        ksort($params);
        foreach ($params as $k => $v) {
            $string .= $k . "=" . $v . "&";
        }
        $string .= '&org_loc='.$path.'&method=POST&secret='.$this->mdasSecret;
        return hash_hmac('sha256', $string, $this->mdasSecret, true);
    }

}