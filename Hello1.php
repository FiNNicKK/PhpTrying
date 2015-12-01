<?php
/**
 * Created by PhpStorm.
 * User: Iliya
 * Date: 25.10.2015
 * Time: 17:26
 */
include 'simple_html_dom.php';
//$url = "http://l2on.net/?c=market&a=item&id=".$i"&setworld=1094" ;
for($i=1869;$i<1870;$i++){
    $url = "http://l2on.net/?c=market&a=item&id=$i&setworld=1094" ;
    echo $url;
    getArrayOfValues($url);
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
function getArrayOfValues($url){
    if ($arResult = getURL($url)) {
        $html = str_get_html($arResult['content']);
        foreach($html->find("h1") as $t) {
            $th  = $t->innertext;
            $title = preg_replace("'<img src[^>]*?>.*?</sup>'si","",$th);
            echo $title;
        }
        foreach($html->find("h2") as $b) {
            $bh  = $b->innertext;
            $bh = preg_replace("'<span class=\"oper-(sell|buy)\">'si","",$bh);
            $sellOrBuy[] = preg_replace("'</span><span class=\"add\">.*?</a>\)</span>'si","",$bh);
            print_r($sellOrBuy);
        }
        $arr=array("Испорченная","Испорченный","Испорченные","Испорченное","&nbsp;");
        $titlee = trim(str_ireplace($arr,"",$title));
        $thIteration = 0;
        $sobIteration = 0;
        $element = 0;
        foreach ($html->find('.tablesorter') as $tablesorter) {
            $colNumber = 0;
            foreach ($tablesorter->find('thead') as $header) {
                foreach ($header->find('th') as $header) {
                    if ($thIteration == 0) {
                        if (($header->plaintext == "Цена")   ||
                            ($header->plaintext == "Кол-во") ||
                            ($header->plaintext == "Мод.")   ||
                            ($header->plaintext == "Замечен")) {
                            $colNumber++;
                            $tableColumnPointers[] = $header->plaintext;
                            print_r($tableColumnPointers);
                        }
                    }
                }
                $thIteration++;
            }
            foreach ($tablesorter->find('tr') as $trshop) {
                $iterator =0;
                $arIterator =0;
                foreach ($trshop->find('td') as $tdr) {
                    if (($iterator <= $colNumber)&&($iterator>0)) {
                        $result[$titlee][$sellOrBuy[$sobIteration]][$element][$tableColumnPointers[$arIterator]] = $tdr->plaintext;
                        $arIterator++;
                    }
                    $iterator++;
                }
                //if ($element!=0)
                //$result[$titlee][$sellOrBuy[$sobIteration]][$element]["Замечен"] = $trshop->find('span',2)->plaintext;
                $element++;
            }
            $sobIteration++;
        }
        print_r($result);
    }
}