<?php
define('SITE_NAME', 'デモ');                                              // サイト名
define('SERVER_NAME', @$_SERVER['SERVER_NAME']);                             // ドメイン（環境によってはSERVER_NAMEでは取得できないケースあり）
define('DOCUMENT_ROOT', substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME']))); // ドキュメントルート取得
define('WWW_ROOT', dirname($_SERVER['SCRIPT_FILENAME']));                   // エントリポイントの設置場所
define('WWW_REL_PATH', substr(WWW_ROOT, strlen(DOCUMENT_ROOT)));            // エントリポイントのドキュメントルートからの相対パス
define('EMAIL_PATH', APPPATH.'email');                                      // メールテンプレートパス
define('EMAIL_LINE', "\r\n");                                               // メールの改行コード
define('DEFAULT_DB_LIMIT', 50);                                             // デフォルトDB Limit
define('DEFAULT_USER_FLG_ADMIN', '0');                                      // ユーザ権限のデフォルト
define('ADMIN_DIR_NAME', '__admin_cms');                                    // 管理画面のディレクトリ名
define('AUTH_TABLE', 'admin_users');                                        // 認証対象のDBテーブル名
define('AUTH_USER_FIELD', 'login_name');                                    // ログインユーザ名のDBフィールド名
define('AUTH_PASSWORD_FIELD', 'password');                                  // ログインパスワードのDBフィールド名
define('AUTH_EMAIL_FIELD', 'email');                                        // E-mailのDBフィールド名
define('DEV_IP_ADDRESS', '202.239.79.109');                                 // 開発環境の接続元IPアドレス
define('UPLOADER_SESSION_ROOT_NAME', 'neuuploader');                        // アップローダと共有するセッション変数のルート(アップローダ側の設定と合わせること)
define('UPLOADER_DIR', WWW_REL_PATH.'/admin/uploader');                     // アップローダのパス
define('UPLOADER_FILE_EXPIRES', 600);                                       // アップローダのローダ経由のファイルキャッシュ生存時間（秒)

define('VIEW_CACHE_EXPIRES', 10);                                            // ビューのキャッシュ時間（分）全てのViewに対する設定ではない

// 疾病CSVのキャッシュファイル場所
define('DISEASE_CSV_CACHE', APPPATH. 'cache/disease.csv');

if (is_cli()) { // シェル実行
    define('FULL_BASE_URL', '');
} else {
    $protocol = is_https() ? 'https' : 'http';
    define('FULL_BASE_URL', $protocol. '://'. SERVER_NAME);
}

// 環境名の定義
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT)
    {
        case 'local':
            define('ENVIRONMENT_NAME','個人ローカル環境');
            break;
        case 'neulocal':
            define('ENVIRONMENT_NAME', 'ノイローカル環境');
            break;
        case 'testing':
            define('ENVIRONMENT_NAME', 'テスト環境');
            break;
        case 'production':
            define('ENVIRONMENT_NAME', '本番環境');
            break;
    }
}

// CMS連携アップローダ -------------------------------------------------------
// ■CMS連携を有効化した時に必要な設定
// 　・.htaccess
//     RewriteCond %{REQUEST_URI} ^[アップロードディレクトリ]
//     RewriteRule ^ ci.php [QSA,E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
//
//   ・アップローダ
//     アップローダ：UPLOAD_CMS_DIR(ファイルパス) / CI: CMS_UPLOADER_UPLOAD_DIR(ディレクトリ名) の設定を合わせる
//     アップローダ：API_SECRET_KEY / CI: CMS_UPLOADER_API_SECRET_KEY が一致するようにする
//
//   ・モデル
//     protected $_uploader_model にディレクトリ名を設定（CMS_UPLOADER_UPLOAD_DIRのサブディレクトリ名として扱われる）
//     protected $_uploader_fields に連携対象となるDBフィールド名を設定する
//
define('CMS_UPLOADER_ENABLED', 0);                                          // CMS連携の有効化
define('CMS_UPLOADER_UPLOAD_DIR', 'cms_upload');                            // アップロードディレクトリ名
// ※htaccessにもrewite設定を追加すること。また、アップローダのディレクトリ設定とも合わせること

define('CMS_UPLOADER_API_SECRET_KEY', 'BnyLjzOKQYmbEyv7sVAURbMJTNwOI4de');   // APIアクセス用のシークレットキー
// ※アップローダの設定と合わせること
// --------------------------------------------------------------------------

// プレビュー ----------------------------------------------------------------
$config['PREVIEW_TYPE'] = 'auth';                                           // user or ip or auth or false(user=認証済みユーザのみ、ip=アクセス元IPによる判定、auth=ベーシック認証による判定)
// ※authの場合、.htaccessにて下記の記述を設定しないとPHPの環境変数にベーシック認証の認証データが
// 入らないので注意すること
// RewriteRule ^(.*)$ ci.php/$1 [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]

$config['PREVIEW_ALLOW_IP'] = ['202.239.79.109'];                           // PREVIEW_TYPE=ipの場合、プレビューを許可するIP
$config['PREVIEW_BASIC_USER'] = 'hyomed';                               // PREVIEW_TYPE=authの場合、ベーシック認証のIDおよびパスワード
$config['PREVIEW_BASIC_PW'] = '2018';
// --------------------------------------------------------------------------

// プロキシ経由の場合も想定してREMOTE_ADDRを取得する
$ip = null;
if (!empty($SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = $ips[0];
}else if (!empty($_SERVER['REMOTE_ADDR'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
}
$config['REMOTE_ADDR'] = $ip;                                               // 接続元IPアドレス

define('PASSWORD_RESET_ENABLED', 1);                                        // 1=パスワードリセット有効
define('PASSWORD_RESET_EXPIRES', 120);                                      // パスワードリセットリンクの有効期限(分)

define('AUTH_RETRY_MAX', 3);                                                // ユーザ認証の最大試行回数(使用しない場合は-1)
define('AUTH_LOCK_RECOVER', 60);                                            // 認証失敗によるロックの解除までの時間(分、無期限の場合は-1)

// 現在時刻定数の設定(デバッグ環境の場合はリクエストからの指定可)
if (is_dev_env()) { // デバッグ環境の場合
    if (!empty($_REQUEST['now']) && preg_match('/^(\d{4})\-(\d{1,2})\-(\d{1,2})(_+(\d{1,2}):(\d{1,2}):(\d{1,2}))?$/', $_REQUEST['now'],$m)) {
        if (isset($m[5])) $tm = $m[5].':'.$m[6].':'.$m[7];
        else $tm = '00:00:00';
        define('NOW', sprintf('%04d-%02d-%02d %s',$m[1],$m[2],$m[3],$tm));
    }
}

if (!defined('NOW')) define('NOW', date('Y-m-d H:i:s'));
define('NOW_TIME', strtotime( NOW ));
define('NOW_DATE', date('Y-m-d',strtotime( NOW )));

// adminかどうかの判定
$req = preg_replace('/\/+/', '/', @$_SERVER['REQUEST_URI']); // スラッシュの重複を除く
$url = preg_replace('#^'.preg_quote(WWW_REL_PATH).'#', '', $req);
if (preg_match('#^/?'.preg_quote(ADMIN_DIR_NAME).'#', $url)) { // 管理画面
    define('ADMIN_MODE', 1);
} else {
    define('ADMIN_MODE', 0);
}
