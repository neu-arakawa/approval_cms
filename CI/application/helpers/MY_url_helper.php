<?php

// $uri: URL
// $protocol: http or https
// $absolute: true=http or https始まりの絶対パス、false=スラッシュ始まりの相対パス
function base_url($uri = '', $protocol = NULL, $absolute=false)
{
    $url = get_instance()->config->base_url($uri, $protocol);
    if (!$absolute) {
        $url = preg_replace('/^(https?:\/\/'.preg_quote(SERVER_NAME).')/', '', $url);
    }
    return $url;
}

// 管理画面用のbase_url取得（自動的に管理画面のディレクトリ名が補完される）
function admin_base_url($uri = '', $protocol = NULL, $absolute=false)
{
    if(strpos($uri, '/')!==0) $uri = '/'. $uri;
    return base_url(ADMIN_DIR_NAME. $uri, $protocol, $absolute);
}

// 管理画面要のリダイレクト（自動的に管理画面のディレクトリ名が補完される）
function admin_redirect($uri = '', $method = 'auto', $code = NULL)
{
    if(strpos($uri, '/')!==0) $uri = '/'. $uri;
    redirect(ADMIN_DIR_NAME. $uri, $method, $code);
}

// history.back() or link
function hbLink($link) {
    if (strpos(@$_SERVER['HTTP_REFERER'], FULL_BASE_URL) === false) {
        return h($link);
    } else {
        return 'javascript:void(0)" onclick="history.back(-1);return false;';
    }
}
