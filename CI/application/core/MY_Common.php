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
    if( empty( $st ) ) $st = strtotime('1970-01-01 00:00:00');
    else $st = strtotime($st);

    if( empty( $ed ) ) $ed = strtotime('2037-12-12 23:59:59');
    else $ed = strtotime($ed);

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

// 診療受付時間パターンの取得
function get_reception_pattern($target_date)
{
    $CI = get_instance();
    $CI->load_model(['Option']);

    //　祝日かどうか
    $holiday = $CI->Option->get_option('holiday');
    if( !empty($holiday) )
        $holiday = explode("\n", $holiday);

    $holiday = array_filter($holiday, function($k) use ($target_date){
        return strtotime($k) == strtotime($target_date);
    });

    if( !empty($holiday)) return  'holiday';

    // 平日か、第1,3,5土曜日か
    $date = new DateTime($target_date);
    $dayOfWeek = $date->format('w');
    $weekNumber = (int)ceil((date('d', 
        strtotime($target_date)) + date('w', strtotime(date('Y-m-01', strtotime($target_date)))) - 1) / 7);

    if ($dayOfWeek === '0' || ($dayOfWeek === '6' && ($weekNumber === 2 || $weekNumber === 4))) {
        return 'holiday';
    }
    else if ($dayOfWeek === '6' && ($weekNumber === 1 || $weekNumber === 3 || $weekNumber === 5)) {
        return 'special_saturday';
    }

    return 'workday';
}

function is_admin(){
    $_SERVER['REQUEST_URI_PATH'] = parse_url(@$_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $_SERVER['REQUEST_URI_PATH']);
    return in_array(ADMIN_DIR_NAME, $segments);
}

function syllabary($str){
	$charset='utf-8';
	if(empty($str)){
		return false;
	}
    $str = mb_substr(trim($str), 0, 1);
	$katakana=array(
		array('あ','アイウエオ'),
		array('か','カキクケコガギグゲゴ'),
		array('さ','サシスセソザジズゼゾ'),
		array('た','タチツテトダヂヅデド'),
		array('な','ナニヌネノ'),
		array('は','ハヒフヘホバビブベボパピプペポ'),
		array('ま','マミムメモ'),
		array('や','ヤユヨ'),
		array('ら','ラリルレロ'),
		array('わ','ワヲン')
	);
	$arr=$katakana;
	$str=mb_convert_kana($str,"C",$charset);
	$head=mb_substr($str,0,1,$charset);
	foreach($arr as $v){
		if(mb_strpos($v[1],$head,0,$charset)!==false){
			return $v[0];
		}
	}
}

$_ = function($s){return $s;};//展開用
