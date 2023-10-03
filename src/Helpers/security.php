<?php
/**
 * @MARK CSRF Protection
 */

function csrf_token($force = false)
{
    if (!$force && isset($_SESSION['_csrf_token'])) {
        return $_SESSION['_csrf_token'];
    }
    $length = 64;
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(($length - ($length % 2)) / 2));
    return $_SESSION['_csrf_token'];
}


function app_key(){
    return base64_decode(defined('APP_KEY') ? APP_KEY : '');
}



function sxss($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sxss_object($obj)
{
    $copy = $obj;
    array_walk_recursive($copy, function (&$leaf) {
        if (is_string($leaf))
            $leaf = sxss($leaf);
    });
    return $copy;
}

function db_escape_string($str)
{
    return DB::ref()->real_escape_string($str);
}
