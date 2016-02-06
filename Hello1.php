<?php
/**
 * Created by PhpStorm.
 * User: Iliya
 * Date: 25.10.2015
 * Time: 17:26
 */
include 'simple_html_dom.php';

$itembook = getRecepiesL2helpme(465, 467);
$pricebook = getPriceL2On(1868, 1871);
//print_r($itembook);
//print_r($pricebook);
/*foreach ( $itembook as $movies ) {
    foreach ($movies as $movie) {
        echo '<dl style="margin-bottom: 1em;">';
        foreach ($movie as $key => $value) {
            echo "<dt>$key</dt><dd>$value</dd>";
        }
        echo '</dl>';
    }
}*/
foreach ($pricebook as $price=>$key)
{
    echo '<dl style="margin-bottom: 1em;">';
    echo "<dt>$price</dt>";
    foreach ($key as $valuea)
    {
        if($valuea=="Продавцы")
            foreach ($value as $key=>$value)
            {
                echo "<dt>$value</dt>";
            }

    }
}


function getURL($url, $cookie = false, $timeout = 30, $maxRedirs = 30)
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
        foreach ($cookie as $key => $value) {
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
    curl_close($ch);
    if ($content) {
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $content, $match);
        $setCookie = array();
        if ($match[1]) {
            foreach ($match[1] as $setCookieString) {
                parse_str($setCookieString, $setCookieUnit);
                $setCookie = array_merge($setCookie, $setCookieUnit);
            }
            $cookie = is_array($cookie) ? array_merge($cookie, $setCookie) : $setCookie;
        }
    }
    if (($response['http_code'] == 301 || $response['http_code'] == 302) AND $maxRedirs) {
        if ($headers = get_headers($response['url']))
            foreach ($headers as $value)
                if (substr(strtolower($value), 0, 9) == "location:")
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

function getPriceL2On($start, $end)
{
    $in = "CP1251";
    $out= "CP1251";
    $result = [];
    for ($i = $start; $i < $end; $i++) {
        $url = "http://l2on.net/?c=market&a=item&id=$i&setworld=1092";
        if ($arResult = getURL($url)) {
            $html = str_get_html($arResult['content']);
            foreach ($html->find("h1") as $t) {
                $th = $t->innertext;
                $title = preg_replace("'<img src[^>]*?>.*?</sup>'si", "", $th);
            }
            foreach ($html->find("h2") as $b) {
                $bh = $b->innertext;
                $bh = preg_replace("'<span class=\"oper-(sell|buy)\">'si", "", $bh);
                $sellOrBuy[] = preg_replace("'</span><span class=\"add\">.*?</a>\)</span>'si", "", $bh);
            }
            $arr = array("Испорченный", "Испорченная", "Испорченное", "Испорченные", "&nbsp;");
            $titlee = trim(str_ireplace($arr, "", $title));
            $sobIteration = 0;
            $element = 0;
            $tableColumnPointers = [];
            foreach ($html->find('.tablesorter') as $tablesorter) {
                $colNumber = 0;
                $thIteration = 0;
                foreach ($tablesorter->find('thead') as $header) {
                    foreach ($header->find('th') as $Subheader) {
                        if ($thIteration == 0) {
                            if (($Subheader->plaintext == "Цена") ||
                                ($Subheader->plaintext == "Кол-во") ||
                                ($Subheader->plaintext == "Мод.") ||
                                ($Subheader->plaintext == "Замечен")
                            ) {
                                $colNumber++;
                                if (!in_array($Subheader->plaintext, $tableColumnPointers)) {
                                    $tableColumnPointers[] = $Subheader->plaintext;
                                }
                            }
                        }
                    }
                    $thIteration++;
                }
                foreach ($tablesorter->find('tr') as $trshop) {
                    $iterator = 0;
                    $arIterator = 0;
                    foreach ($trshop->find('td') as $tdr) {
                        if (($iterator <= $colNumber) && ($iterator > 0)) {
                            $result[$titlee][$sellOrBuy[$sobIteration]][$element - 1][$tableColumnPointers[$arIterator]] = $tdr->plaintext;
                            $arIterator++;
                        }
                        $iterator++;
                    }
                    $element++;
                }
                /*$amountOfElements = count($result,COUNT_RECURSIVE);
                print_r($amountOfElements);*/
                $sobIteration++;
            }

        }
    }
    return ($result);
}

function getRecepiesL2helpme($start1, $end1)
{
    $in = "CP1251";
    $out= "CP1251";
    $itemBook = [];
    for ($f = $start1; $f < $end1; $f++) {
        $url = "http://l2help.me/ru/recipes/$f";
        if ($arResult1 = getURL($url)) {
            $html = str_get_html($arResult1['content']);
            foreach ($html->find("h1") as $t) {
                $recipe = $t->innertext;
            }
            $arr = array("Рецепт: ", " (100%)", " (70%)");
            $itemName = trim(str_ireplace($arr, "", $recipe));
            foreach ($html->find('div[class=media hidden]') as $div) {
                $div->class = "media";
            }
            $element = 0;
            foreach ($html->find('div.media-body') as $div) {
                $amount = count($div->children());
                if (($amount == 1)) {
                    foreach ($div->find('p') as $p) {
                        foreach ($p->find('a') as $a) {
                            $resourseName = $a->plaintext;
                        }
                        foreach ($p->find('strong') as $strong) {
                            $resourseCount = $strong->find('span', 0)->plaintext;
                        }
                        //$resourseName = iconv($in,$out,$resourseName);
                        $itemBook[$itemName][$element] = ["resourseName" => $resourseName, "resourseCount" => $resourseCount];
                        $element++;
                    }
                }
            }
        }
        //echo($html);
        /*for ($it = 0; $it < count($itemBook[$itemName]); $it++) {
            for ($k = $it; $k < count($itemBook[$itemName]); $k++) {
                if (($itemBook[$itemName][$it]['resourseName'] == $itemBook[$itemName][$k]['resourseName']) && ($it != $k)) {
                    $itemBook[$itemName][$it]['resourseCount'] += $itemBook[$itemName][$k]['resourseCount'];
                    unset($itemBook[$itemName][$k]);
                    $itemBook[$itemName] = array_values($itemBook[$itemName]);
                }
            }
        }*/
    }
        return ($itemBook);

}
