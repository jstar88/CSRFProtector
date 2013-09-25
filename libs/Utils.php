<?php

function remove_qs_key($url, $key)
{
    $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
    if (endsWith($url, '?'))
    {
        $url = substr($url, 0, -1);
    }
    return $url;
}
function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}
function contains($string, $search)
{
    return strpos($a, $search) !== false;
}

function getRequestUrlWithoutKey($keys = array())
{
    $url = $_SERVER["REQUEST_URI"];
    foreach ($keys as $key)
    {
        $url = remove_qs_key($url, $key);
    }
    return $url;
}
function isHrefToThisServer($href)
{
    return parse_url($href, PHP_URL_HOST) == $_SERVER["HTTP_HOST"] || parse_url($href, PHP_URL_HOST) == null;
}

function isPost()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}
function isGet()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

?>