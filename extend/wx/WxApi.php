<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/25
 * Time: 18:51
 */

namespace wx;

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
            \session('wxAccessToken',[
                'access_token'=>$accessToken['access_token'],
                'time'=>time() + $accessToken['expires_in'],
            ]);
        }
        return $accessToken['access_token'];
    }

    public function arrayToXml($arr, $root)
    {
        $xml = '<' . $root . '>'.PHP_EOL;
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= '<' . $key . '>'. $this->arrayToXml($val, $root) . '</' . $key . '>'.PHP_EOL;
            } else {
                $xml .= '<' . $key . '>'. $val . '</' . $key . '>'.PHP_EOL;
            }
        }
        $xml .= '</' . $root . '>'.PHP_EOL;
        $a = mb_detect_encoding($xml);
        return $xml;
    }

    public function xmlToArray($xml){
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    /**
     * Curl
     * @param $url
     * @param string $post
     * @param string $cookie
     * @param bool $returnCookie
     * @return mixed|string
     */
    public function curl_http($url, $post = '', $dataType='json',$ssl = true, $cookie = '', $returnCookie = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "https://api.blockhuaxia.com");
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $post = $dataType == 'xml' ? $post:json_encode($post);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        if($ssl){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA颁布的证书
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
            curl_setopt($curl, CURLOPT_SSLCERT, './cert/apiclient_cert.pem');//证书位置
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
            curl_setopt($curl, CURLOPT_SSLKEY, './cert/apiclient_key.pem');//证书位置
            curl_setopt($curl, CURLOPT_CAINFO, 'PEM');
            curl_setopt($curl, CURLOPT_CAINFO, './cert/rootca.pem');
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
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