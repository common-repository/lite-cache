<?php

global $cache_stop;
$cache_stop = false;

// Use this only if you can't or don't want to modify the .htaccess
if ($_SERVER['REQUEST_METHOD'] == 'POST')
    return false;
if ($_SERVER['QUERY_STRING'] != '')
    return false;
if (defined('SID') && SID != '')
    return false;
if (isset($_COOKIE['cache_disable']))
    return false;

if (_REJECTAGENTSENABLED_) {
    if (preg_match('/(_REJECTAGENTS_)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        $cache_stop = true;
        return false;
    }
}

$lc_group = '';
foreach ($_COOKIE as $n => $v) {
    if (substr($n, 0, 20) == 'wordpress_logged_in_') {
        $lc_group = '-user';
    } else if (substr($n, 0, 12) == 'wp-postpass_') {
        $cache_stop = true;
        return false;
    } else if (_NOCOMMENTATOR_ && substr($n, 0, 14) == 'comment_author') {
        $cache_stop = true;
        return false;
    }
}

if (lc_is_mobile()
) {
// Bypass
    if (_MOBILE_ == 2)
        return false;
    $lc_group = '-mobile' . $lc_group;
}

//$lc_file = ABSPATH . 'wp-content/cache/lite-cache' . $_SERVER['REQUEST_URI'] . '/index' . $lc_group . '.html';
$lc_uri = preg_replace('/[^a-zA-Z0-9\.\/\-_]+/', '_', $_SERVER['REQUEST_URI']);
$lc_uri = preg_replace('/\/+/', '/', $lc_uri);
$lc_uri = rtrim($lc_uri, '.-_/');
if (empty($lc_uri) || $lc_uri[0] != '/')
    $lc_uri = '/' . $lc_uri;

$lc_file = '_FOLDER_' . '/' . strtolower($_SERVER['HTTP_HOST']) . $lc_uri . '/index' . $lc_group . '.html';
if (!is_file($lc_file))
    return false;

$lc_file_time = filemtime($lc_file);

if (_MAX_AGE_ > 0 && $lc_file_time < time() - (_MAX_AGE_ * 3600))
    return false;

if (array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER)) {
    $lc_if_modified_since = strtotime(preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]));
    if ($lc_if_modified_since >= $lc_file_time) {
        header("HTTP/1.0 304 Not Modified");
        flush();
        die();
    }
}

header('Content-Type: text/html;charset=UTF-8');
header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lc_file_time) . ' GMT');

if (_MOBILE_ == 0) {
    header('Vary: Accept-Encoding');
} else {
    header('Vary: Accept-Encoding,User-Agent');
}
header('Cache: must-revalidate');
if (_NOGZIP_ == 0 && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    header('Content-Encoding: gzip');

    header('Content-Length: ' . filesize($lc_file . '.gz'));
    
    echo file_get_contents($lc_file . '.gz');
//readfile($lc_file . '.gz');
} else {
    header('Content-Length: ' . filesize($lc_file));
    
    echo file_get_contents($lc_file);
//readfile($lc_file);
}
flush();
die();

function lc_is_mobile() {
// Do not detect
    if (_MOBILE_ == 0)
        return false;
    if (defined('IS_PHONE'))
        return IS_PHONE;
    return preg_match('/(_AGENTS_)/i', strtolower($_SERVER['HTTP_USER_AGENT']));
}
