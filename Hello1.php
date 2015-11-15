<?php
/**
 * Created by PhpStorm.
 * User: Iliya
 * Date: 25.10.2015
 * Time: 17:26
 */
include 'simple_html_dom.php';
$url = "http://l2on.net/?c=market&a=item&id=1869&setworld=1092" ;
$regex = "/\<div id=\"shops\"\>(.*)\<\/tr\>/";
if ($arResult = getURL($url)) {
    //echo iconv("windows-1251","utf-8",$arResult['content']);
    //preg_match_all($regex, iconv("windows-1251", "utf-8", $arResult['content']), $matches);
    //print_r($matches);
    $ret = $arResult['content']->find('.tablesorter');
}


function getURL($url, $cookie = false, $timeout = 30, $maxRedirs = 7)
{
    $ch = curl_init();
    $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $header[] = "Connection: keep-alive";
    $header[] = "Keep-Alive: 300";
    $header[] = "Accept-Charset: Windows-1251;q=0.7,*;q=0.7";
    $header[] = "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3";
    $header[] = "Pragma: ";
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    if (is_array($cookie)) {
        foreach($cookie as $key => $value ) {
            $cookieString[] = "{$key}={$value}";
        };
        $cookie = implode('; ', $cookieString);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $content = curl_exec($ch);
    $response = curl_getinfo($ch);
    curl_close ($ch);
    if ($content) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $content, $match);
        $setCookie = array();
        if ($match[1]) {
            foreach($match[1] as $setCookieString) {
                parse_str($setCookieString, $setCookieUnit);
                $setCookie = array_merge($setCookie, $setCookieUnit);
            }
            $cookie = is_array($cookie) ? array_merge($cookie, $setCookie) : $setCookie;
        }
    }
    if (($response['http_code'] == 301 || $response['http_code'] == 302) AND $maxRedirs) {
        if ($headers = get_headers($response['url']))
            foreach($headers as $value)
                if (substr( strtolower($value), 0, 9 ) == "location:")
                    return getURL(trim(substr($value, 9, strlen($value))), $cookie, $timeout, --$maxRedirs);
    }
    if ($content) {
        $data['set_cookie'] = $cookie;
        $data['response'] = $response;
        $data['content'] = $content;
        return $data;
    } else
        return false;
}
