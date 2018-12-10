<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 20:43
 */

function arrayToXml($arr, $root)
{
    $xml = '<' . $root . '>';
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $xml .= '<' . $key . '>' . $this->arrayToXml($val, $root) . '</' . $key . '>';
        } else {
            $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
        }
    }
    $xml .= '</' . $root . '>';
    return $xml;
}

function xmlToArray($xml){
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xmlstring),true);
    return $val;
}

/**
 * curl_http
 * @param $url
 * @param string $post
 * @param string $cookie
 * @param int $returnCookie
 * @return mixed|string
 */
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
    curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
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