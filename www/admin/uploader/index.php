<?php
ini_set('display_errors',1);
require_once 'core/core.php';
class ExNeuUploaderCore extends NeuUploaderCore{

	//
	// ユーザ定義の認証処理
	// 認証に成功した場合はtrueを、失敗した場合はfalseを返す
	//
	// protected function authorize(){
	// 	//
	// 	// ここに認証処理を書く
	// 	//
	// 	return false;
	// }
}

// デフォルト設定(core.php)を上書きするための設定
//
// ★注意★
// のちのちのアップデートのため、
// core.php上で設定は変更せず、
// new ExNeuUploaderCoreの引数として設定を行って下さい
$options = array(
	'SESSION_AUTH' => true,
	'SESSION_COOKIE_NAME' => 'cms',
	'SESSION_INI' => array( // ini_set用の設定
        'session.cookie_lifetime' => 0,
        'session.cookie_secure' => false,
        'session.cookie_httponly' => false,
        'session.save_path' => '../../CI/application/sessions/',
    ),
    'PHP_LOG' => true,
	'UPLOAD_DIR' => WWW_ROOT. '/upload',             // アップロードディレクトリ(フルパス)
	'UPLOAD_CMS_DIR' => WWW_ROOT. '/cms_upload',             // アップロードディレクトリ(フルパス)
	'THUMB_DIR' => WWW_ROOT. '/upload/_thumbs',     // サムネイル保存ディレクトリ(フルパス)
	// 'MAX_FILE_UPLOADS' => 2,
	// 'MKDIR_ENABLED' => false
);
new ExNeuUploaderCore( $options );
