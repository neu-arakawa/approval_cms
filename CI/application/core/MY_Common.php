<?php
// アプリの共通関数定義

// デバッグ環境かどうか
function is_dev_env()
{
    return ENVIRONMENT === 'local' || ENVIRONMENT === 'neulocal' ||
            $_SERVER['REMOTE_ADDR'] === DEV_IP_ADDRESS;
}

// selectのkey > value変換
function opt($values, $options, $glue=' / ')
{
    if( empty( $values ) && $values !== '0' && $values !== 0 ) return '';

    $out = array();
    if( !is_array( $values ) ) $values = array( $values );
    foreach( $values as $v ){
        if( !empty( $options[$v] ) ){
            $out[] = h( $options[$v] );
        }
    }
    return implode( $glue, $out );
}


if (!function_exists('h')) {
    // HTMLエスケープ関数
    function h($var, $default = null)
    {
        $ret = '';
        if (empty($var) && !is_null($default)) {
            $ret = $default;
        } else {
            $ret = $var;
        }
        return html_escape($ret);
    }
}
function eh($var, $default = null)
{
    if (empty($var) && !is_null($default)) {
        echo nl2br(h($default));
    } else {
        echo nl2br(h($var));
    }
}

// メールテンプレート用
function em($var, $default = null)
{
    if (empty($var) && !empty($default)) {
        echo $default."\n";
    } else {
        echo $var."\n";
    }
}

// 文字列を一定の長さで切る
function tr($str, $maxlen, $suffix='...')
{
    $ret = $str;
    if (mb_strlen($str) > $maxlen) {
        $ret = mb_substr($str, 0, $maxlen). $suffix;
    }
    return $ret;
}

function dateA($str = 'now', $separator = '/')
{
    $format = 'Y'.$separator.'m'.$separator.'d';
    return date($format, strtotime($str));
}

function wday_jp($wday) {
    return [0=>'日','月','火','水','木','金','土'][$wday] ?? null;
}

// 配列を一次元にする
function array_flatten(array $arr) {
    $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));
    $rtn = [];
    foreach ($it as $k => $v) {
        $rtn[$k] = $v;
    }
    return $rtn;
}

// 配列から指定プロパティだけのシンプルな配列を作る
function array_o2a($list, $value_key)
{
    $rtn = [];

    if (is_array($list)) foreach ($list as $k => $v) {
        if (is_object($v)) {
            $rtn[$k] = @$v->$value_key;
        } else {
            $rtn[$k] = @$v[$value_key];
        }
    }

    return $rtn;
}

// キーワードを全角・半角・大文字・小文字に変換する
function convert_keyword( $word )
{
    $options = ['a','A','k','K'];
    $result[] = $word;
    // debug("orig:$word");
    foreach( $options as $opt ){
        $conv_word = mb_convert_kana( $word, $opt );
        // debug("opt=$opt:{$conv_word}");
        if( $conv_word == $word ) continue;

        $result[] = $conv_word;
    }
    $funcs = ['mb_strtoupper','mb_strtolower'];
    foreach( $options as $opt ){
        foreach( $funcs as $func ){
            $conv_word = $func( $word );
            $conv_word = mb_convert_kana( $conv_word, $opt );
            // debug("func=$func opt=$opt:{$conv_word}");

            if( $conv_word == $word ) continue;
            $result[] = $conv_word;
        }
    }
    return array_unique($result);
}

// 現在が指定の期間内かどうか判定する
function during_term( $st=null, $ed=null )
{
    if( empty( $st ) ) {
        $st = strtotime('1970-01-01 00:00:00');
    } else {
        if (preg_match('/^\d{4}[\-\]\d{1,2}[\-\/]\d{1,2}$/', $st)) {
            // 時刻なしの場合
            $st .= ' 00:00:00';
        }
        $st = strtotime($st);
    }

    if( empty( $ed ) ) {
        $ed = strtotime('2037-12-12 23:59:59');
    } else {
        if (preg_match('/^\d{4}[\-\/]\d{1,2}[\-\/]\d{1,2}$/', $ed)) {
            // 時刻なしの場合
            $ed .= ' 23:59:59';
        }
        $ed = strtotime($ed);
    }

    return $st <= NOW_TIME && NOW_TIME <= $ed;
}

function show_403($page = '', $log_error = TRUE)
{
    $_error =& load_class('Exceptions', 'core');

    if (is_cli()) {
        $heading = 'Forbidden';
    } else {
        $heading = '403 Forbidden';
    }
    $message = 'The page you requested was not allowed.';

    if ($log_error) {
        log_message('error', $heading.': '.$page);
    }

    echo $_error->show_error($heading, $message, 'error_404', 403);
    exit(4); // EXIT_UNKNOWN_FILE
}

function show_400($page = '', $log_error = TRUE)
{
    $_error =& load_class('Exceptions', 'core');

    if (is_cli()) {
        $heading = 'Bad Request';
    } else {
        $heading = '400 Bad Request';
    }
    $message = 'Your access was a bad request.';

    if ($log_error) {
        log_message('error', $heading.': '.$page);
    }

    echo $_error->show_error($heading, $message, 'error_404', 400);
    exit(4); // EXIT_UNKNOWN_FILE
}

// モデルのロード(view用)
function load_model ($name)
{
    $CI = get_instance();

    if (preg_match('/^Admin/', $name)) { // Admin
        $basepath = 'admin/'.$name;
    } else {
        $basepath = $name;
    }

    if (file_exists(APPPATH.'models/'.$basepath.'.php')) {
        $CI->load->model($basepath);
        return $CI->{$name};
    }
    return null;
}

// 配列内でいずれかの項目が指定されているかどうか
function any_in_array ($fields, $array, $index=null) {
    if (!is_array($fields) || !is_array($array)) return false;

    foreach ($fields as $f) {
        if ($index===null) {
            $v = !empty($array[$f]) ? $array[$f] : null;
        } else {
            $v = !empty($array[$f][$index]) ? $array[$f][$index] : null;
        }
        if ($v) return true;
    }
    return false;
}

function pascalize($string)
{
    $string = strtolower($string);
    $string = str_replace('_', ' ', $string);
    $string = ucwords($string);
    $string = str_replace(' ', '', $string);
    return $string;
}
function ddl($var)
{
    if (is_string($var) == false) {
        $var = print_r($var, true);
    }

    log_message('debug', $var);
}

if (!function_exists('pr')) {
    function pr($array){
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}


function is_admin(){
    $_SERVER['REQUEST_URI_PATH'] = parse_url(@$_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $_SERVER['REQUEST_URI_PATH']);
    return in_array(ADMIN_DIR_NAME, $segments);
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

if( !function_exists('array_key_last') ) {
    function array_key_last(array $array) {
        if( !empty($array) ) return key(array_slice($array, -1, 1, true));
    }
}

$_ = function($s){return $s;};//展開用
