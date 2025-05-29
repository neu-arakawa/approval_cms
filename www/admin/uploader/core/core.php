<?php
//
// NeuUploaderCore
//
// require php >= 5.3
// require IE >= 9（ファイルのドラッグ&ドロップは IE >= 10）
// require gd（サムネイル作成機能を使用する場合）
//
//

// アクション定義
define( 'ACTION_DISPLAY', 'disp' );             // ファイルの一覧表示
define( 'ACTION_DELETE', 'delete' );            // ファイルの削除
define( 'ACTION_UPLOAD', 'upload' );            // ファイルのアップロード
define( 'ACTION_DOWNLOAD', 'download' );        // ファイルのダウンロード
define( 'ACTION_RENAME', 'rename' );            // ファイルのリネーム
define( 'ACTION_MKDIR', 'mkdir' );              // ディレクトリの追加
define( 'ACTION_RMDIR', 'rmdir' );              // ディレクトリの削除
define( 'ACTION_DIRLIST', 'dirlist' );          // ディレクトリ一覧の取得

// CMS連携用API
define( 'API_CMS_MOVE_DIR', 'cmsmovedir' );     // ディレクトリ移動
define( 'API_CMS_DELETE', 'cmsdelete' );        // 削除
define( 'API_CMS_REPLICATE', 'cmsreplicate' );  // 複製

// ドキュメントルートの取得
define( 'WWW_ROOT', substr( $_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME']) ) );
define( 'APP_DIR', dirname(__DIR__) );

require_once('settings.php');

class NeuUploaderCore{

    private $defaultOptions = null;
    private $dirFilePath = null;
    private $dirWebPath = null;
    private $templateWebPath = null;
    protected $requests = null;
    protected $dirRelPath = null;

    /*
     * コンストラクタ
     */
    function __construct( $options=array() ){

        // デフォルトの設定
        // 必要があればindex.php上で設定を行って下さい
        $this->defaultOptions = array(
            'UPLOAD_MODE'               => 'normal',                            // アップロードモード(normal=通常 cms=CMS連携モード)
            // アクセス制限モードは、CMS側のデータの有効期限に紐付けてデータをアップロードしたい場合に使用。
            // 基本的にはベースのディレクトリにアクセス制限をかけた上で、
            // データ読み出し用のスクリプトを介することで実現する

            'ENCODING'                  => 'UTF-8',                             // 文字コード mb_xxx 系の関数用

            'CSRF_CHECK'                => true,                                // CSRFのチェック処理有効
            'CSRF_TOKEN_NAME'           => 'csrf_token',                        // CSRFのトークン名

            'ALLOWED_EXTENSIONS'        => array(                               // 許可するファイル拡張子
                                'txt', 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'pdf', 'doc', 'docx',
                                'xls', 'xlsx', 'ppt', 'pptx', 'mp4', 'zip',
                            ),
            'MAX_UPLOAD_FILE_SIZE'      => null,                                // アップロードファイルサイズ上限(例：5MB nullの場合はphp.iniの設定から取得)
            'MAX_POST_SIZE'             => null,                                // POSTのサイズ上限(例：30MB nullの場合はphp.iniの設定から取得)
            'MAX_FILE_UPLOADS'          => null,                                // 同時にアップロードできるファイル数
            'MAX_DIR_LEVEL'             => 5,                                   // ディレクトリ階層の最大数（1〜）
            'IMAGE_RESIZE_ENABLED'      => true,                                // 指定された場合アップロードされた画像をリサイズする。
            'IMAGE_RESIZE'              => array( 2000,2000 ),                  // リサイズ時の縦・横の定義。縦、横それぞれが指定のサイズに収まるように処理される。処理を行わない場合はNULLを設定する。
            'IMAGE_RESIZE_EXTENSIONS'   => array( 'jpg', 'jpeg', 'jpe', 'png', 'gif' ), // リサイズ対象の拡張子

            'UPLOAD_DIR'                => WWW_ROOT. '/upload',                 // アップロードディレクトリ(フルパス)
            'UPLOAD_CMS_DIR'            => WWW_ROOT. '/cms_upload',             // アップロードディレクトリ(フルパス)※CMS連携用。通常のディレクトリと分けたいとき用
            'THUMB_DIR'                 => null,                                // サムネイル保存ディレクトリ(フルパス)
            'THUMB_SIZE'                => array( 240, 240 ),                   // サムネイルサイズ（横、縦）アップロード画像の長辺を基準に縮小処理を行う
            'THUMB_ENABLED'             => true,                                // サムネイルの自動生成有効
            'THUMB_EXTENSIONS'          => array( 'jpg', 'jpeg', 'jpe', 'png', 'gif' ), // サムネイル作成対象の拡張子

            'DIR_PERMISSION'            => 0755,                                // ディレクトリ作成時のパーミッション
            'FILE_PERMISSION'           => 0777,                                // ファイルアップロード時のパーミッション

            'DEFAULT_DIR_GET_KEY'       => 'dir',                               // 初期ディレクトリを指定するGETパラメータ名
            'AUTO_MKDIR'                => false,                               // ディレクトリの自動作成

            'MKDIR_ENABLED'             => true,                                // ディレクトリの作成許可

            'MULTIBYTE_FNAME_ENABLED'   => false,                               // マルチバイトのファイル名を許可するか

            'TEMPLATE_DIR'              => APP_DIR. '/template',
            'TEMPLATE_INDEX'            => 'index.php',                         // テンプレートパス(フロントエンド)
            'TEMPLATE_CONTENT'          => 'content.php',                       // テンプレートパス(コンテンツ)

            'ROOT_DIR_NAME'             => 'フォルダ',

            'OVERWRITE_MODE'            => 'rename',                            // 同名ファイルの扱い(overwrite:上書き rename:リネーム)

            // UPLOAD_MODE=cmsの場合に設定する
            'CMS_UPLOAD_SUBDIR'         => '',                                  // アップロードするサブディレクトリ名（通常セッションで指定する）
            'CMS_UPLOAD_DATA_ID'        => '',                                  // データのID（サブディレクトリ直下にIDのディレクトリが作成される）
            'CMS_NEW_ID_PREFIX'         => 'new_',                              // 新規ID用に設定するディレクトリのプレフィックス
            'CMS_DIR_GC_TIME'           => 86400,                               // 新規ID用のアップロードディレクトリを削除するまでの時間

            'DEBUG'                     => true,                                // デバッグフラグ(offの場合はデバッグ環境からのアクセスからでもすべて本番と同じ挙動になる)
            'DEBUG_HOST_REGEX'          => '^([^\.]+)\.example\d?\.([^\.]+)',   // デバッグサーバ判定用正規表現
            'DEBUG_IP'                  => array('160.86.231.249'),             // デバッグ用リモートIP

            'PHP_LOG'                   => false,                               // PHPのログ出力
            'LOG_DIR'                   => __DIR__. '/../log',                  // ログの出力ディレクトリ

            'SESSION_AUTH'              => false,                               // セッションによる認証
            // SESSION_AUTHが設定されていて、
            // authorize()が定義されている場合は自動で認証を行わず、
            // authorize()の結果を優先する
            'SESSION_ROOT_NAME'         => 'neuuploader',                       // セッションの変数のルート
            'SESSION_AUTH_KEY'          => 'uploader_auth',                     // セッション認証のセッションキー
            'SESSION_COOKIE_NAME'       => 'uploader_sess',                     // セッションのcookie名

            'API_SECRET_KEY'            => 'BnyLjzOKQYmbEyv7sVAURbMJTNwOI4de',  // APIモードでアクセスする場合のキー

            'SESSION_INI'               => array( // ini_set用の設定
                                                'session.cookie_lifetime' => 0,
                                                'session.cookie_secure' => false,
                                                'session.cookie_httponly' => true
                                           ),

            // 拡張子とファイルアイコンの関連付け
            'FILE_ICON' => array(
                'ai'=>'ai.svg', 'avi'=>'avi.svg', 'bmp'=>'bmp.svg', 'css'=>'css.svg',
                'doc'=>'doc.svg', 'docx'=>'doc.svg', 'fla'=>'fla.svg', 'gif'=>'gif.svg', 'html'=>'html.svg',
                'jpg'=>'jpg.svg', 'jpe'=>'jpg.svg', 'jpeg'=>'jpg.svg', 'js'=>'js.svg', 'mov'=>'mov.svg',
                'mp3'=>'mp3.svg', 'mp4'=>'mp4.svg', 'pdf'=>'pdf.svg', 'png'=>'png.svg', 'ppt'=>'ppt.svg',
                'pptx'=>'ppt.svg', 'psd'=>'psd.svg', 'rar'=>'rar.svg', 'svg'=>'svg.svg', 'txt'=>'txt.svg',
                'wav'=>'wav.svg', 'wmv'=>'wmv.svg', 'xls'=>'xls.svg', 'xlsx'=>'xls.svg', 'zip'=>'zip.svg',
            ),
        );

        $this->initialize( $options );
    }

    /*
     * 初期化処理
     */
    private function initialize( $user_opts=array() ){

        // ユーザ定義の設定を上書きマージ
        $options = array_merge(
            $this->defaultOptions,
            $user_opts
        );
        NeuSettings::write( $options );

        // リクエスト値を保存
        $this->requests = $_REQUEST;

         // 一旦セッション関連の設定を行いセッションを開始する
        if( !empty( $options['SESSION_COOKIE_NAME'] ) ){
            $options['SESSION_INI']['session.name'] = $options['SESSION_COOKIE_NAME'];
        }
        NeuSettings::write('SESSION_INI', $options['SESSION_INI']);
        start_session(); // セッション開始

        // 動作環境チェック
        if( version_compare( phpversion(), '5.3.0', '<' ) ){
            // サポートバージョン未満
            $this->printError( 'this php version:'. phpversion() .' is not supported' );
        }

        // セッションに保存されたデータから設定を転記
        $keys = ['UPLOAD_MODE', 'CMS_UPLOAD_SUBDIR', 'CMS_UPLOAD_DATA_ID'];
        foreach ($keys as $k) {
            if(($val=get_cms_session_item($k))!==null){
                $options[$k] = $val;
            }
        }

        if (!empty($this->requests['api']) &&
            ($this->requests['api']===API_CMS_MOVE_DIR || $this->requests['api']===API_CMS_REPLICATE || $this->requests['api']===API_CMS_DELETE) ) {
            // APIアクセス時
            $options['UPLOAD_MODE'] = 'cms'; // CMS連携モードに
        }

        if( $options['UPLOAD_MODE']==='cms'){ // CMS連携モード時
            $options['UPLOAD_DIR'] = $options['UPLOAD_CMS_DIR']; // アップロードディレクトリを自動的に変更
            $options['THUMB_DIR'] = $options['UPLOAD_DIR']. '/_thumbs/';
            $options['MKDIR_ENABLED'] = false;  // ディレクトリ作成は不可
        }

        // アップロード、サムネイルディレクトリの正規化＆存在チェック
        if( $options['UPLOAD_DIR'] ){
            if (!file_exists($options['UPLOAD_DIR']) && !mkdir( $options['UPLOAD_DIR'], $options['DIR_PERMISSION'], true )) {
                // アップロードディレクトリが存在しない場合は作成を試みる
                $this->printError( 'upload directory couldn\'t be created');
            }

            if( ( $options['UPLOAD_DIR'] = realpath( $options['UPLOAD_DIR'] ) ) === false ){
                // アップロードディレクトリ不正
                $this->printError( 'upload directory is not valid' );
            }

            if (empty($options['THUMB_DIR'])) { // サムネイルディレクトリが指定されなかった場合
                $options['THUMB_DIR'] = $options['UPLOAD_DIR']. '/_thumbs/';
            }
        }
        NeuSettings::write( $options );

        // APIアクセス時処理
        if (!empty($this->requests['api'])) {
            if (empty($this->requests['secret_key']) || $this->requests['secret_key']!==$options['API_SECRET_KEY']) {
                // 不正なシークレットキー
                $this->printError('secret key is not valid');
            }

            switch($this->requests['api']) {
                case API_CMS_MOVE_DIR:
                    $this->moveCmsDirectory();
                    break;
                case API_CMS_REPLICATE:
                    $this->copyCmsDirectory();
                    break;
                case API_CMS_DELETE:
                    $this->deleteCmsDirectory();
                    break;
            }
            return;
        }

        if( DEF('SESSION_AUTH') ){ // セッション認証有効

            // コールバック呼び出し
            $authorized = false;
            if( method_exists( $this, 'authorize' ) ){ // 認証処理が定義されている
                $authorized = $this->authorize();
            }else{ // 認証処理が定義されていない
                $authorized = !empty( $_SESSION[DEF('SESSION_ROOT_NAME')][DEF('SESSION_AUTH_KEY')] );
            }

            if( !$authorized ){ // セッション認証失敗
                $this->printError( '認証に失敗しました' );
            }
        }

        if( $options['UPLOAD_MODE']==='cms'){ // CMS連携モード

            if (empty($options['CMS_UPLOAD_SUBDIR'])) {
                // サブディレクトリ名が設定されていない
                $this->printError('cms sub directory is not set');
            }

            $data_id = null;
            if (!empty($options['CMS_UPLOAD_DATA_ID'])) { // IDの指定あり（更新）
                $data_id = $options['CMS_UPLOAD_DATA_ID'];
            } else { // IDの指定なし（新規作成）
                if (($data_id=get_cms_session_item('NEW_ID'))===null) {
                    $data_id = $options['CMS_NEW_ID_PREFIX'].substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 7);
                    set_cms_session_item('NEW_ID', $data_id); // セッションに書き込み
                }
            }

            $cmsdir = $this->getCmsDir($options['CMS_UPLOAD_SUBDIR'], $data_id);
            $this->requests['dir'] = $cmsdir; // CMS連携の場合はセッションで指定されたディレクトリ固定

            $path = $options['UPLOAD_DIR'] . '/'. $this->getCmsDir($options['CMS_UPLOAD_SUBDIR'], $data_id);
            if( !file_exists( $path ) && !mkdir( $path, $options['DIR_PERMISSION'], true ) ){ // サムネイルディレクトリが存在せず、さらに作成に失敗した
                $this->printError( 'cms sub directory cannot be created' );
            }

            // CMS連携モードの場合はサムネイルディレクトリはUPLOAD_DIRのサブディレクトリに自動的に設定
            $options['THUMB_DIR'] = $options['UPLOAD_DIR']. '/_thumbs';
        }

        if( $options['TEMPLATE_DIR'] ){
            if( ( $options['TEMPLATE_DIR'] = realpath( $options['TEMPLATE_DIR'] ) ) === false ){
                // テンプレートディレクトリ不正
                $this->printError( 'template directory is not valid' );
            }
        }

        if( empty($options['MAX_POST_SIZE']) ){
            // POSTの最大サイズが設定されていなければ、
            // php.iniの設定から取得する
            $ini = ini_get( 'post_max_size' ). 'B';
            $options['MAX_POST_SIZE'] = $ini;
        }
        if( empty($options['MAX_UPLOAD_FILE_SIZE']) ){
            // ファイルのアップロードサイズが設定されていなければ、
            // php.iniの設定から取得する
            $ini = ini_get( 'upload_max_filesize' ). 'B';
            $options['MAX_UPLOAD_FILE_SIZE'] = $ini;
        }
        if( empty($options['MAX_FILE_UPLOADS']) ){
            // ファイルのアップロードサイズが設定されていなければ、
            // php.iniの設定から取得する
            $options['MAX_FILE_UPLOADS'] = ini_get( 'max_file_uploads' );
        }

        if( $options['THUMB_DIR'] ){
            if( !file_exists( $options['THUMB_DIR'] ) && !mkdir( $options['THUMB_DIR'], $options['DIR_PERMISSION'], true ) ){ // サムネイルディレクトリが存在せず、さらに作成に失敗した
                $this->printError( 'thumbnail directory cannot be created' );
            }
            if( ( $options['THUMB_DIR'] = realpath( $options['THUMB_DIR'] ) ) === false ){
                // アップロードディレクトリ不正
                $this->printError( 'thumbnail directory is not valid' );
            }
        }

        NeuSettings::write( $options );

        log_setting(); // ログ関連設定



        // 古いPHP用
        // magic_quotes_gpcが有効な場合、
        // エスケープ用のバックスラッシュを削除する
        if( version_compare( phpversion(), '5.4.0', '<' ) ){
            if( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc()===1 ){
                function strip_magic_quotes_slashes($arr){
                    return is_array( $arr ) ?
                    array_map( 'strip_magic_quotes_slashes', $arr ) :
                    stripslashes( $arr );
                }
                $this->requests = strip_magic_quotes_slashes( $this->requests );
            }
        }

        $this->cmsDirGC(DEF('UPLOAD_DIR')); // CMS連携用の不要ディレクトリを削除

        // アクションのチェックおよび取得
        $action = null;
        $act_list = array(
            ACTION_DISPLAY, ACTION_DOWNLOAD, ACTION_UPLOAD, ACTION_DELETE, ACTION_RENAME, ACTION_MKDIR, ACTION_RMDIR, ACTION_DIRLIST
        );
        if( empty( $this->requests['action'] ) || !in_array( $this->requests['action'] , $act_list ) ){
            // アクションが未指定、または不正な場合はデフォルトで「表示」アクションにする
            $action = ACTION_DISPLAY;
        }else{
            $action = $this->requests['action'];
        }

        if( !is_ajax() && $action!=ACTION_UPLOAD && $action!=ACTION_DOWNLOAD ){
            // ajax経由のアクセスでない、かつファイルアップロードでない、かつファイルダウンロードでない
            // フロントエンドページを表示して終了

            // GETパラメータにて、ディレクトリが指定されていれば
            // 初期ディレクトリとしてリダイレクトにより移動を行う
            if (!empty($_GET[DEF('DEFAULT_DIR_GET_KEY')])) {
                $dir = $_GET[DEF('DEFAULT_DIR_GET_KEY')];
                $dir = preg_replace('/^\//', '', $dir);

                $parsed = parse_url($_SERVER['REQUEST_URI']);
                $kvs = explode('&', $parsed['query']);
                $_kvs = [];
                foreach ($kvs as $kv) {
                    $ar = explode('=', $kv);
                    if ($ar[0] === DEF('DEFAULT_DIR_GET_KEY')) continue;
                    $_kvs[] = "{$ar[0]}={$ar[1]}";
                }
                $redirect_url = $parsed['path']. '?'. implode('&', $_kvs). '#'. $dir;
                header('Location: '. $redirect_url);
            }

            if( !is_readable( DEF('TEMPLATE_DIR').'/'. DEF('TEMPLATE_INDEX') ) ){
                $this->printError( "templete file include error" );
            }

            // キャッシュ無効
            header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
            header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' );
            header( 'Cache-Control: no-store, no-cache, must-revalidate' );
            header( 'Cache-Control: post-check=0, pre-check=0', false );
            header( 'Pragma: no-cache' );

            require_once( DEF('TEMPLATE_DIR').'/'. DEF('TEMPLATE_INDEX') );
            return;
        }

        if( !is_readable( DEF('TEMPLATE_DIR').'/'. DEF('TEMPLATE_CONTENT') ) ){
            // テンプレートページが読み込み不可ならエラー
            $this->printError( "templete file include error" );
        }

        if( !empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > size_to_byte(DEF('MAX_POST_SIZE')) ){
            // POSTされたデータの容量がPOST上限サイズを超過した
            $this->returnResult( null, '送信データの上限を超過したため、処理を中断しました' );
        }

        // カレントディレクトリのチェック
        $errors = array();
        $req_dir = $this->validateItemDir( $errors );
        if( !empty( $errors ) ){ // エラーあり
            $this->returnResult( null, $errors );
        }

        // ディレクトリのファイルパス
        $this->dirFilePath = realpath( DEF('UPLOAD_DIR'). '/'. $req_dir );

        // ディレクトリの相対パス
        $this->dirRelPath = $req_dir;

        // Web上のパス
        $this->dirWebPath = substr( $this->dirFilePath, strlen(WWW_ROOT) );

        // テンプレートパス
        $this->templateWebPath = substr( DEF('TEMPLATE_DIR'), strlen(WWW_ROOT) );

        // アクションごとに処理の分岐
        switch( $action ){
            case ACTION_DISPLAY: // 表示
                $this->display();
                break;
            case ACTION_UPLOAD: // アップロード
                $this->upload();
                break;
            case ACTION_DELETE: // 削除
                $this->delete();
                break;
            case ACTION_RENAME: // リネーム
                $this->rename();
                break;
            case ACTION_MKDIR: // ディレクトリ作成
                $this->makeDirectory();
                break;
            case ACTION_RMDIR: // ディレクトリ削除
                $this->removeDirectory();
                break;
            case ACTION_DOWNLOAD: // ダウンロード
                $this->download();
                break;
            case ACTION_DIRLIST: // ディレクトリ一覧
                $list = array();
                $this->getDirectoryList( '', $list );
                $this->returnResult( $list );
        }
    }

    //
    // ディレクトリ、ファイルの一覧取得・表示
    //
    private function display(){

        $disp_list = array();
        if( $h_dir = opendir( $this->dirFilePath ) ){
            // ディレクトリオープン成功

            // 指定ディレクトリの走査
            while( ($fname = readdir($h_dir)) !== false ){
                // カレントディレクトリ、親ディレクトリは排除
                if( $fname === '.' || $fname === '..' ) continue;

                // ファイルシステム上のパスを取得
                $file_path = realpath( $this->dirFilePath.'/'.$fname );

                if( !is_readable( $file_path ) ) continue; // 読み込み不可の項目は非表示
                if( strpos( DEF( 'THUMB_DIR' ), $file_path ) !== false ) continue; // サムネイルディレクトリは無視する

                // ファイル情報取得
                $item_info = $this->getItemInfo( $fname );
                if( $item_info ) $disp_list[] = $item_info;
            }
        }else{
            // ディレクトリオープン失敗
            $this->returnResult( null, 'ディレクトリの表示に失敗しました' );
        }

        // ファイルリストのソート
        function cmp( $a, $b ){
            $as = $a['is_dir'] ? 10 : 0;
            $bs = $b['is_dir'] ? 10 : 0;

            // 更新日時の降順
            $as += $a['modified'] < $b['modified'] ? 0 : 1;
            $bs += $b['modified'] < $a['modified'] ? 0 : 1;

            if ($as < $bs) {
                return 1;
            } elseif ($as > $bs) {
                return -1;
            } else {
                return 0;
            }
        }
        uasort( $disp_list, 'cmp' );

        // カレントディレクトリの階層を取得
        $dir_level = $this->getDirectoryLevel( $this->dirRelPath );
        $mkdir_enabled = true;
        if( $dir_level >= DEF( 'MAX_DIR_LEVEL' ) || !is_writable( $this->dirFilePath ) ){
            // ディレクトリ階層が最大に達している場合、または
            // 書き込み権限がない場合はディレクトリ作成不可
            $mkdir_enabled = false;
        }
        $upload_enabled = is_writable( $this->dirFilePath );

        // _d($disp_list);

        $params = array(
            'curr_dir' => $this->dirRelPath,
            'disp_list' => $disp_list,
            'mkdir_enabled' => $mkdir_enabled,
            'upload_enabled' => $upload_enabled,
        );

        $this->returnResult( get_template( DEF('TEMPLATE_DIR').'/'.DEF('TEMPLATE_CONTENT'), $params ) );
    }

    //
    // アップロード処理
    //
    private function upload(){
        $errors = array();

        // ファイルシステム上のディレクトリパスを取得
        $dir_path = DEF('UPLOAD_DIR'). '/'. $this->dirRelPath;
        if( !is_writable( $dir_path ) ){
            // ディレクトリに書き込み権限なし
            $this->returnResult( null, 'ディレクトリに書き込み権限がありません' );
        }

        // ファイルの指定チェック
        if( empty( $_FILES['upload'] ) ){
            // ファイルの指定が無い場合はエラー
            $this->returnResult( null, 'ファイルが指定されていません' );
        }

        $save_files = array();
        if( !is_array( $_FILES['upload']['name'] ) ){
            $save_files[] = array(
                'name' => $_FILES['upload']['name'],
                'type' => $_FILES['upload']['type'],
                'tmp_name' => $_FILES['upload']['tmp_name'],
                'error' => $_FILES['upload']['error'],
                'size' => $_FILES['upload']['size'],
            );
        }else{
            $count = count($_FILES['upload']['name']);
            if( $count > DEF('MAX_FILE_UPLOADS') ){
                // ファイル数が上限を超過
                $count = DEF('MAX_FILE_UPLOADS');
                $errors[] = "一度にアップロードできるファイル数の上限(".DEF('MAX_FILE_UPLOADS')."個)を超過しています";
            }
            for( $i=0; $i<$count; $i++ ){
                $save_files[] = array(
                    'name' => $_FILES['upload']['name'][$i],
                    'type' => $_FILES['upload']['type'][$i],
                    'tmp_name' => $_FILES['upload']['tmp_name'][$i],
                    'error' => $_FILES['upload']['error'][$i],
                    'size' => $_FILES['upload']['size'][$i],
                );
            }
        }

        $res_files = array();
        foreach( $save_files as $file ){
            $info = pathinfo( $file['name'] );

            // アップロード先ディレクトリの書き込み権限チェック
            $save_file_path = $this->dirFilePath. '/'. $file['name'];

            if( !$this->isValidFilePath( $save_file_path ) ){ // ファイルパスが不正
                $errors[] = "ファイル名はスペースの入らない半角英数字、-（ハイフン）、_（アンダーバー）のみをご利用ください";
                continue;
            }

            // ファイルサイズチェック
            $max_byte = size_to_byte( DEF('MAX_UPLOAD_FILE_SIZE') );
            if( $file['size'] > $max_byte ){
                $errors[] = "{$file['name']}: ファイルサイズの上限(".byte_to_size($max_byte).")を超過しています";
                continue;
            }

            // 同名ファイルのチェック
            if( file_exists( $save_file_path ) ){
                // 同名ファイルあり

                switch( DEF('OVERWRITE_MODE') ){ // 上書きモード
                    case 'overwrite': // 上書き
                        if( !is_writable( $dir_path ) ){
                            // 書き込み権限なし
                            $errors[] = "{$file['name']}: ファイルの上書きのための書き込み権限がありません";
                        }

                        $ret = move_uploaded_file( $file['tmp_name'], $save_file_path );
                        if( !$ret ){
                            $errors[] = "{$file['name']}: ファイルの上書き保存に失敗しました";
                        }
                        break;

                    case 'rename': // リネーム
                        for( $i=1; $i<10; $i++ ){
                            $save_file_path = "{$dir_path}/{$info['filename']}({$i}).{$info['extension']}";
                            if( !file_exists( $save_file_path ) ){
                                // リネーム後のファイルが存在しない

                                $ret = move_uploaded_file( $file['tmp_name'], $save_file_path );
                                if( !$ret ){
                                    $errors[] = "{$file['name']}: ファイルのリネーム保存に失敗しました";
                                }

                                break 2;
                            }
                        }

                        break;
                }
            }else{
                // 同名ファイルなし

                $ret = move_uploaded_file( $file['tmp_name'], $save_file_path );
                if( !$ret ){
                    $errors[] = "{$file['name']}: ファイルの保存に失敗しました";
                    continue;
                }
            }

            if( DEF( 'IMAGE_RESIZE_ENABLED' ) && !empty($info['extension']) && in_array($info['extension'],  DEF('IMAGE_RESIZE_EXTENSIONS') ) ){
                // リサイズ有効、かつリサイズ対象の拡張子の場合
                // リサイズ処理を行う
                $this->makeResizeImage( $this->dirRelPath, $file['name'] );
            }

            // ファイルパーミッションの変更
            if( file_exists( $save_file_path ) ){
                @chmod( $save_file_path, DEF( 'FILE_PERMISSION' ) );
            }

            if( DEF( 'THUMB_ENABLED' ) && !empty($info['extension']) && in_array($info['extension'],  DEF('THUMB_EXTENSIONS') ) ){
                // サムネイル生成有効、かつサムネイル作成対象の拡張子の場合
                // サムネイル生成処理を行う
                $this->makeThumbnail( $this->dirRelPath, $file['name'] );
            }

            $res_files[] = $this->getItemInfo( $file['name'] );
        }

        $this->returnResult( array('files'=>$res_files), $errors );
    }

    /*
     * 削除
     */
    private function delete(){

        $file_path = DEF('UPLOAD_DIR'). '/'. $this->dirRelPath. '/'. $this->requests['file_name'];
        if( !$this->isValidFilePath( $file_path ) ){
            $this->returnResult( null, 'ファイル名が不正です' );
        }

        if( !is_deletable( $file_path ) ){
            $this->returnResult( null, 'ファイルの削除権限がありません' );
        }

        // ファイルの削除
        delete_item( $file_path );

        // サムネイルの削除
        $thumb_path = DEF('THUMB_DIR'). '/'. $this->dirRelPath. '/'. $this->requests['file_name'];
        if( is_deletable( $thumb_path ) ){ // サムネイル削除権限あり
            delete_item( $thumb_path );
        }

        $this->returnResult();
    }

    /*
     * ファイルのリネーム
     */
    private function rename(){

        if( empty( $this->requests['curr_name'] ) ){
            $this->returnResult( null, '変更対象のファイル名が指定されていません' );
        }
        if( empty( $this->requests['new_name'] ) ){
            $this->returnResult( null, '変更後のファイル名が指定されていません' );
        }

        $old_path = DEF('UPLOAD_DIR'). '/'. $this->dirRelPath.'/'.$this->requests['curr_name'];
        $new_path = DEF('UPLOAD_DIR'). '/'. $this->dirRelPath.'/'.$this->requests['new_name'];

        if( !$this->isValidFilePath( $old_path ) ||
            !$this->isValidFilePath( $new_path ) ){
            $this->returnResult( null, 'ファイル名が不正です' );
        }

        if( !is_renameable( $old_path ) ){ // リネーム権限なし
            $this->returnResult( null, 'ファイルのリネーム権限がありません' );
        }

        if( $old_path !== $new_path ){

            if( file_exists( $new_path ) ){
                // 既に同名のファイルがある
                $this->returnResult( null, 'すでに同一のファイル名が存在します' );
            }

            $ret = rename( $old_path, $new_path ); // リネーム処理

            $old_path = DEF('THUMB_DIR'). '/'. $this->dirRelPath.'/'. $this->requests['curr_name'];
            $new_path = DEF('THUMB_DIR'). '/'. $this->dirRelPath.'/'. $this->requests['new_name'];

            // サムネイルのリネーム
            if( is_renameable( $old_path ) ){ // リネーム権限あり
                $ret = rename( $old_path, $new_path );
            }
        }

        // リネーム後のファイル情報を取得
        $item = $this->getItemInfo( $this->requests['new_name'] );

        // ディレクトリ一覧を取得
        $dirlist = array();
        $this->getDirectoryList( '', $dirlist );

        $this->returnResult( array('item'=>$item, 'dir_list'=>$dirlist) );
    }

    /*
     * ディレクトリ作成
     */
    private function makeDirectory(){

        if( !DEF('MKDIR_ENABLED') ){
            $this->returnResult( null, 'ディレクトリの作成が許可されていません' );
        }

        $errors = array();

        // 現ディレクトリのチェック
        $dir_level = $this->getDirectoryLevel( $this->dirRelPath );
        if( $dir_level >= DEF( 'MAX_DIR_LEVEL' ) ){
            $this->returnResult( null, 'ディレクトリの階層が深すぎます(最大:'.DEF( 'MAX_DIR_LEVEL' ).')' );
        }

        // ファイルシステム上のディレクトリパスを取得
        $dir_path = $this->dirFilePath;
        if( !is_writable( $dir_path ) ){
            // ディレクトリに書き込み権限なし
            $this->returnResult( null, 'ディレクトリの書き込み権限がありません' );
        }
        if( empty( $this->requests['dir_name'] ) ){
            $this->returnResult( null, 'ディレクトリ名が指定されていません' );
        }

        $dir_path =  $dir_path. '/'. $this->requests['dir_name'];
        if( !$this->isValidFilePath( $dir_path ) ){
            $this->returnResult( null, 'ディレクトリ名が不正です' );
        }
        if( file_exists( $dir_path ) ){
            $this->returnResult( null, '同一のディレクトリ名が存在しています' );
        }

        if( !mkdir( $dir_path ) ){
            $this->returnResult( null, 'ディレクトリの作成に失敗しました' );
        }

        // ディレクトリパーミッションの変更
        @chmod( $dir_path, DEF( 'DIR_PERMISSION' ) );

        // ディレクトリ一覧を取得
        $dirlist = array();
        $this->getDirectoryList( '', $dirlist );

        $this->returnResult( array('dir_list'=>$dirlist) );
    }

    /*
     * ディレクトリ削除
     */
    private function removeDirectory(){

        // 削除対象のディレクトリ名の指定チェック
        if( empty( $this->requests['dir_name'] ) ){
            $this->returnResult( null, 'ディレクトリ名が指定されていません' );
        }else if( strpos($this->requests['dir'], '..') !== false ){
            $this->returnResult( null, 'ディレクトリ名が不正です' );
        }

        $dir_path =  $this->dirFilePath. '/'. $this->requests['dir_name'];
        if( !$this->isValidFilePath( $dir_path ) ){
            $this->returnResult( null, 'ディレクトリ名が不正です' );
        }

        if( !is_deletable( $dir_path ) ){
            $this->returnResult( null, 'ディレクトリの削除権限がありません' );
        }

        // ディレクトリ削除
        delete_item( $dir_path );

        // サムネイルディレクトリの削除
        $thumb_path = DEF('THUMB_DIR'). '/'. $this->dirRelPath. '/'. $this->requests['dir_name'];
        if( is_deletable( $thumb_path ) ){ // 削除権限あり
            delete_item( $thumb_path );
        }

        // ディレクトリ一覧を取得
        $dirlist = array();
        $this->getDirectoryList( '', $dirlist );

        $this->returnResult( array('dir_list'=>$dirlist) );
    }

    private function download(){

         if( empty( $this->requests['file_name'] ) ){
            $this->returnResult( null, 'ファイル名が指定されていません' );
        }

        $file_path = $this->dirFilePath. '/'. $this->requests['file_name'];
        if( !$this->isValidFilePath( $file_path ) ){
            $this->returnResult( null, 'ファイル名が不正です' );
        }

        if( !is_readable( $file_path ) ){
            $this->returnResult( null, 'ファイルの読み込み権限がありません' );
        }

        header( 'Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($this->requests['file_name']) );
        header( 'Content-Length: '. filesize($file_path) );
        header( 'Content-Type: '. mime_content_type($file_path) );
        readfile( $file_path );
        exit();
    }

    /*
     * ディレクトリ名のバリデート
     */
    private function validateItemDir( &$errors=array() ){

        $req_dir = '';
        if( !empty($this->requests['dir']) ){
            // ディレクトリの指定あり

            // ディレクトリトラバーサル対策
            if( !check_dir_traversal( $this->requests['dir'] ) ){
                $errors[] = 'ディレクトリ名が不正です';
                return false;
            }

            $req_dir = $this->requests['dir'];

            // URLデコード（日本語用）
            $req_dir = urldecode( $req_dir );

            // 先頭および末尾のスラッシュを削除
            $req_dir = preg_replace('/^\/+/', '', $req_dir );
            $req_dir = preg_replace('/\/+$/', '', $req_dir );

            // 重複するスラッシュ削除
            $req_dir = preg_replace('/\/{2,}/', '/', $req_dir );

        }else{
            // ディレクトリの指定なし
            $req_dir = '';
        }

        $dir_level = $this->getDirectoryLevel( $req_dir );
        if( $dir_level > DEF( 'MAX_DIR_LEVEL' ) ){
            // 使用可能な最大階層を超えている
            $errors[] = 'ディレクトリの階層が深すぎます';
            return false;
        }
        $full_path = DEF( 'UPLOAD_DIR' ). '/'. $req_dir;
        if (DEF('AUTO_MKDIR') && !file_exists($full_path)) {
            // ディレクトリが存在しない
            if (!mkdir($full_path, DEF('DIR_PERMISSION'), true)) {
                $errors[] = 'ディレクトリの作成に失敗しました';
                return false;
            }
        }

        $dir_path = realpath($full_path);
        if( !is_dir($dir_path) || !is_readable($dir_path) ){ // ディレクトリでない、または読み込み権限なし
            // trigger_error($dir_path);
             $errors[] = 'ディレクトリが正しくありません';
             return false;
        }
        if( strpos( $dir_path,DEF( 'THUMB_DIR' ) ) !== false ){ // サムネイルディレクトリが指定された
            $errors[] = 'サムネイルディレクトリが指定されました';
            return false;
        }

        return $req_dir;
    }

    /*
     * ファイル名のバリデート
     */
    private function isValidFilePath( $fpath ){

        $fname = basename( $fpath );
        if( !check_dir_traversal( $fpath ) ) return false;  // ディレクトリトラバーサルチェック
        if( strlen($fpath) > 1023 ) return false;           // パスの長さ制限
        if( strlen($fname) > 255 ) return false;            // ファイル名の長さ制限
        if( strpos($fname, '/') !== false || strpos($fname, ' ') !== false || strpos($fname, '　') !== false ) return false;   // 使用禁止文字
        if( $fname=='.' || $fname=='..' ) return false;

        $finfo = pathinfo( $fpath );
        // 拡張子チェック
        $allowed_ext = DEF( 'ALLOWED_EXTENSIONS' );
        if( !empty($finfo['extension']) && !in_array($finfo['extension'], $allowed_ext ) ){
            return false;
        }

        if( !DEF('MULTIBYTE_FNAME_ENABLED') ){ // マルチバイトファイル名不許可
            if( mb_strlen( $fpath ) !== strlen( $fpath ) ) return false;
        }

        return true;
    }

    /*
     * ディレクトリの階層を取得する
     */
    private function getDirectoryLevel( $dir ){
        $dir_level = count( preg_split( '/\//', $dir, -1, PREG_SPLIT_NO_EMPTY ) ) + 1;
        return $dir_level;
    }

    /*
     * リサイズ画像の作成
     */
    private function makeResizeImage( $dir, $file_name ){
        // 仮の名前でリサイズ保存
        $src_path = DEF( 'UPLOAD_DIR' ). '/'. $dir. '/'. $file_name;
        $dest_path = DEF( 'UPLOAD_DIR' ). '/'. $dir. '/___tmp___'. $file_name;
        $ret = $this->resizeImage( $src_path, $dest_path, DEF( 'IMAGE_RESIZE' ) );
        if( $ret ){
            // 仮の名前を元の名前に戻す
            $ret = rename( $dest_path, $src_path );
        }
        return $ret;
    }

    /*
     * サムネイル画像の作成
     */
    private function makeThumbnail( $dir, $file_name ){
        $src_path = DEF( 'UPLOAD_DIR' ). '/'. $dir. '/'. $file_name;
        $dest_path = DEF( 'THUMB_DIR' ). '/'. $dir. '/'. $file_name;

        return $this->resizeImage( $src_path, $dest_path, DEF( 'THUMB_SIZE' ) );
    }

    /*
     * 画像リサイズ処理
     */
    private function resizeImage( $src_path, $dest_path, $resize_def ){
        if( !function_exists( 'gd_info' ) ) return false; // GD未インストールなら終了
        if( !is_array( $resize_def ) || count( $resize_def )!=2 ) return false; // 設定が無効なら終了

        if( !file_exists( dirname( $dest_path ) ) ){
            // 書き出し先のディレクトリが存在しない
            // ディレクトリの作成
            if( !mkdir( dirname( $dest_path ), DEF('DIR_PERMISSION'), true ) ){
                // 作成失敗
                // TODO: log
                return false;
            }
        }

        if( (file_exists( $dest_path ) && !is_writable( $dest_path )) ||
                !is_writable( dirname( $dest_path ) ) ){
            // 書き出し先のディレクトリに書き込み権限がない
            // TODO: log
            return false;
        }

        // オリジナル画像のサイズを取得
        $src_img_info = getimagesize( $src_path );
        if( $src_img_info === false ) return false;
        list( $src_width, $src_height ) = $src_img_info;

        $dest_width = $src_width;
        $dest_height = $src_height;

        // サムネイル画像：元画像の各辺の比率を計算
        $rate_w = $resize_def[0] / $src_width;
        $rate_h = $resize_def[1] / $src_height;

        if( $rate_w < 1 || $rate_h < 1 ){
            // 定義されたサムネイルサイズの縦横（のいずれか）が、対応する元画像の縦横（のいずれか）よりも小さい
            if( $rate_h < $rate_w ){
                // 縦長画像
                // 縦をサムネイル定義サイズの縦に合わせ、
                // 横サイズを計算する
                $dest_height = floor( $resize_def[1] );
                $dest_width = floor( $src_width * $rate_h );
            }else{
                // 横長画像
                // 横をサムネイル定義サイズの横に合わせ
                // 縦サイズを計算する
                $dest_width = floor( $resize_def[0] );
                $dest_height = floor( $src_height * $rate_w );
            }
        }

        if( $dest_width == $src_width ){
            // サムネイル作成の必要なし
            if( file_exists( $dest_path ) ){
                // 不要なサムネイルを削除する
                $ret = unlink( $dest_path );
            }
            return false;
        }
        // 画像処理関数名を生成
        $create_func = '';
        $output_func = '';
        switch( $src_img_info['mime'] ){
            case 'image/png':
                $create_func = 'imagecreatefrompng';
                $output_func = 'imagepng';
                break;
            case 'image/jpeg':
                $create_func = 'imagecreatefromjpeg';
                $output_func = 'imagejpeg';
                break;
            case 'image/gif':
                $create_func = 'imagecreatefromgif';
                $output_func = 'imagegif';
                break;
        }
        if( empty( $create_func ) ) return false;

        if( !$image_src = $create_func( $src_path ) ){
            // 画像生成失敗
            return false;
        }

        $image_dest = imagecreatetruecolor( $dest_width, $dest_height );
        imagecopyresampled( $image_dest, $image_src, 0, 0, 0, 0,
                $dest_width, $dest_height, $src_width, $src_height );

        // exif情報から必要であれば回転させる
        $exif = @exif_read_data($src_path);
        if (!empty($exif['Orientation'])) {
            $angle = 0;
            switch ($exif['Orientation']) {
            case 3:
                $angle = 180;
                break;
            case 6:
                $angle = 270;
                break;
            case 8:
                $angle = 90;
                break;
            }
            if (!empty($angle)) {
                $image_dest = imagerotate($image_dest, $angle, 0);
            }
        }

        if( !$output_func( $image_dest, $dest_path ) ){
            // 画像変換＆コピー
            return false;
        }

        // パーミッション変更
        if( file_exists( $dest_path ) ){
            @chmod( $dest_path, DEF( 'FILE_PERMISSION' ) );
        }

        return true;
    }

    /*
     * サムネイルが生成されているかチェック
     */
    private function getThumbnailWebPath( $dir, $file_name ){

        if( !DEF( 'THUMB_DIR' ) || !is_array( DEF( 'THUMB_SIZE' ) ) ) return false; // サムネイル設定が無効なら終了

        // ファイルシステム上のディレクトリパスを取得
        $file_path = DEF( 'THUMB_DIR' ). '/'. $dir;
        $file_path = preg_replace( '/\/+$/', '', $file_path );
        $file_path .= '/'. $file_name;

        $finfo = pathinfo( $file_path );
        if( !in_array( $finfo['extension'], array( 'jpg', 'jpe', 'jpeg', 'png', 'gif' ) ) ){
            // ファイルが指定の画像形式でない
            return false;
        }
        if( !is_readable( $file_path ) ){
            // ファイルの読み込み権限がない
            return false;
        }

        // Web上のパスを取得
        $web_path = substr( $file_path, strlen(WWW_ROOT) );
        return $web_path;
    }

    /*
     * ディレクトリリストを返す
     */
    private function getDirectoryList( $dir='', &$list=null ){
        if (is_null($list)) {
            return false;
        }
        if( empty($dir) ) $path = DEF('UPLOAD_DIR');
        else $path = DEF('UPLOAD_DIR'). '/'. $dir;

        $files = array_diff( scandir( $path ), array('.','..') );
        foreach( $files as $file ){
            if( $path.'/'.$file === DEF('THUMB_DIR') ) continue;

            if( is_dir( $path. '/'. $file ) ){
                $list[$file] = array();
                $this->getDirectoryList( $dir. '/'. $file, $list[$file] );
            }
        }

        if( empty( $dir ) ){
            // トップレベルディレクトリを追加
            $list = array(
                DEF('ROOT_DIR_NAME') => $list,
            );
        }
    }

    /*
     * アップロードディレクトリの移動（リネーム処理）
     * CMS連携でのデータ新規作成後に、確定したIDに応じたディレクトリに変更する場合を想定
     */
    private function moveCmsDirectory() {
        $sub_dir = $this->requests['sub_dir'];
        $src_id = $this->requests['src_id'];
        $dest_id = $this->requests['dest_id'];

        $src_path =  DEF('UPLOAD_DIR').'/'.$this->getCmsDir($sub_dir, $src_id);
        $dest_path = DEF('UPLOAD_DIR').'/'.$this->getCmsDir($sub_dir, $dest_id);

        if (!file_exists($src_path) || file_exists($dest_path)) {
            // 移動元のディレクトリが存在しない、または
            // 移動先のディレクトリがすでに存在する
            // var_dump([$src_path, $dest_path]);
            $this->printError('faild to move cms directory');
        } else {
            if (rename($src_path, $dest_path)===false) {
                $this->printError('faild to move cms directory');
            }
        }

        // サムネイルの移動処理
        $src_path =  DEF('THUMB_DIR').'/'.$this->getCmsDir($sub_dir, $src_id);
        $dest_path = DEF('THUMB_DIR').'/'.$this->getCmsDir($sub_dir, $dest_id);
        // _d($src_path);
        // _d($dest_path);

        if (file_exists($src_path) || !file_exists($dest_path)) {
            rename($src_path, $dest_path);
        }
    }
    /*
     * アップロードディレクトリのコピー
     * CMS連携でのデータ複製時の処理を想定
     */
    private function copyCmsDirectory() {
        $sub_dir = $this->requests['sub_dir'];
        $src_id = $this->requests['src_id'];
        $dest_id = $this->requests['dest_id'];

        $src_path =  DEF('UPLOAD_DIR').'/'.$this->getCmsDir($sub_dir, $src_id);
        $dest_path = DEF('UPLOAD_DIR').'/'.$this->getCmsDir($sub_dir, $dest_id);

        if (!file_exists($src_path) || file_exists($dest_path)) {
            // コピー元のディレクトリが存在しない、または
            // コピー先のディレクトリがすでに存在する
            // var_dump([$src_path, $dest_path]);
            $this->printError('faild to copy cms directory');
        } else {
            dir_copy($src_path, $dest_path);
        }
        // _d($src_path);
        // _d($dest_path);

        // サムネイルのコピー処理
        $src_path =  DEF('THUMB_DIR').'/'.$this->getCmsDir($sub_dir, $src_id);
        $dest_path = DEF('THUMB_DIR').'/'.$this->getCmsDir($sub_dir, $dest_id);

        if (file_exists($src_path) || !file_exists($dest_path)) {
            dir_copy($src_path, $dest_path);
        }
    }

    /*
     * アップロードディレクトリの削除
     * CMS連携でのデータ削除時の処理を想定
     */
    private function deleteCmsDirectory() {
        $sub_dir = $this->requests['sub_dir'];
        $del_id = $this->requests['del_id'];

        $del_path =  DEF('UPLOAD_DIR').'/'.$this->getCmsDir($sub_dir, $del_id);

        if (!file_exists($del_path)) {
            // 削除対象のディレクトリが存在しない
            $this->printError('faild to delete cms directory');
        } else {
            dir_delete($del_path);
        }

        // サムネイルの削除処理
        $del_path =  DEF('THUMB_DIR').'/'.$this->getCmsDir($sub_dir, $del_id);
        if (file_exists($del_path)) {
            dir_delete($del_path);
        }
    }

    private function getCmsDir($subdir, $data_id, $file=null) {
        $subdir = preg_replace('/^\/+|\/+$/', '', $subdir);

        $subdir .= '/'. $data_id;
        if (!empty($file)) $subdir .= '/'. $file;

        return $subdir;
    }

    /*
     * ファイル情報を取得する
     */
    private function getItemInfo( $fname ){

        $file_path = $this->dirFilePath .'/'. $fname;
        $stat = stat($file_path); // ファイル情報取得

        if( is_dir($file_path) ){ // ディレクトリ

            // ディレクトリに含まれるファイル数取得
            $file_in_dir = array_diff( scandir($file_path), array('.','..') );
            $dir = $this->dirRelPath;
            $dir .= $this->dirRelPath ? '/' : '';
            $dir .= $fname;

            return array(
                'is_dir' => true,
                'web_path' => $this->dirWebPath. '/'. $fname,
                'file_name' => $fname,
                'modified' => $stat ? $stat['mtime'] : null,
                'is_deletable' => is_deletable( $file_path ),
                'is_readable' => is_readable( $file_path ),
                'is_writable' => is_writable( $file_path ),
                'is_executable' => is_executable( $file_path ),
                'is_renameable' => is_renameable( $file_path ),
                'dir' => $dir,
                'file_count' => count( $file_in_dir ),
            );
        }else{// ファイル
             // 指定された拡張子以外は排除
            $info = pathinfo( $file_path );
            $allowed_ext = DEF( 'ALLOWED_EXTENSIONS' );
            if( !empty($info['extension']) && in_array($info['extension'], $allowed_ext) ){

                $is_img = in_array($info['extension'], DEF('THUMB_EXTENSIONS') );
                if( $is_img ){ // 画像の場合
                    $thumb_path = $this->getThumbnailWebPath( $this->dirRelPath, $fname ); // サムネイル画像のパス
                }else{ // それ以外の場合
                    $icons = DEF('FILE_ICON');
                    $thumb_path = $this->templateWebPath. '/img/';
                    $thumb_path .= !empty($icons[$info['extension']]) ? $icons[$info['extension']] : 'file.svg';
                }
                $item_path = $this->dirWebPath. '/'. $fname;

                return array(
                    'is_dir' => false,
                    'web_path' => $item_path,
                    'file_name' => $fname,
                    'modified' => $stat ? $stat['mtime'] : null,
                    'is_deletable' => is_deletable( $file_path ),
                    'is_readable' => is_readable( $file_path ),
                    'is_writable' => is_writable( $file_path ),
                    'is_executable' => is_executable( $file_path ),
                    'is_renameable' => is_renameable( $file_path ),
                    'ext' => $info['extension'],
                    'is_image' => $is_img,
                    'size' => $stat ? byte_to_size($stat['size']) : null,
                    'thumb_web_path' => $thumb_path ? $thumb_path : $item_path,
                );
            }
        }

        return null;
    }

    private function cmsDirGC($dir_path) {
        if (!DEF('CMS_NEW_ID_PREFIX')) return;

        if ($dh = opendir($dir_path)) {
            // ディレクトリを走査
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($dir_path. '/'. $file)) {
                    // ディレクトリの場合は再帰処理
                    // dir_delete($dir_path. '/'. $file);
                    if (preg_match('#^'.preg_quote(DEF('CMS_NEW_ID_PREFIX')).'#', $file)) {
                        $tm = filemtime($dir_path.'/'.$file);

                        if (time() > $tm + DEF('CMS_DIR_GC_TIME')) { // 一定時間が経過している
                            // 不要ディレクトリとして削除する
                            dir_delete($dir_path.'/'.$file);
                        }

                    } else {
                        $this->cmsDirGC($dir_path.'/'.$file);
                    }
                }
            }
            closedir($dh);
        }
    }

    /*
     * リダイレクト
     */
    private function redirect( $url ){
        header( 'Location: '. $url );
    }

    /*
     * エラーページ表示
     */
    private function printError( $msg ){
        header( "HTTP/1.1 400 Bad Request" );
        print $msg. "\n";
        if( DEF('PHP_LOG') ){ // PHPログ有効
            trigger_error( $msg, E_USER_ERROR ); // エラー生成
        }
        exit();
    }

    /*
     * 結果データを返す
     */
    private function returnResult( $result=array(), $errors=array() ){

        $errors = !empty($errors) && !is_array($errors) ? array($errors) : $errors;
        if( empty($errors ) ) $errors = null;

        if( !is_ajax() ){ // 非AJAX
            // フラッシュメッセージを設定し、
            // ページ再表示のためにリダイレクト
            $req_dir = $this->validateItemDir();
            flash_message( $errors );
            //$this->redirect( $_SERVER['PHP_SELF']. '#'. $req_dir );
            $this->redirect( $_SERVER['REQUEST_URI']. '#'. $req_dir );
        }else{
            // 結果、エラーをjson形式で出力
            echo json_encode( array(
                'result' => $result,
                'errors' => $errors,
            ) );
        }
        exit();
    }
}

/*
 * HTMLエスケープ処理
 */
function h( $text, $double=true ){
    return htmlspecialchars($text, ENT_QUOTES, DEF('ENCODING'), $double);
}

/*
 * ディレクトリ、またはファイルが削除可能か調べる
 */
function is_deletable( $fpath ) {

    // 親ディレクトリに読み／書き権限がなければ削除不可
    $par_dir = dirname( $fpath );
    if( !is_readable($par_dir) || !is_writable($par_dir) ) return false;

    // ファイルが存在しなければ終了
    if( !file_exists( $fpath ) ) return false;

    // ディレクトリでなければ削除可
    if( is_dir( $fpath ) ){
        // ディレクトリ

        // サブディレクトリについて、読み／書き権限がなければ削除不可
        $stack = array( $fpath );
        while( $dir=array_pop($stack) ) {
            if( !is_readable($dir) || !is_writable($dir) ){
                return false;
            }

            $files = array_diff( scandir($dir), array('.','..') );
            foreach( $files as $file ){
                $d = "$dir/$file";
                if( is_dir($d) ) {
                    $stack[] = "$dir/$file";
                }
            }
        }

    }else{
        // ファイル
        return true;
    }

    return true;
}


/*
 * ファイルおよびディレクトリがリネーム可能か調べる
 */
function is_renameable( $fpath ){

    // 親ディレクトリに読み／書き権限がなければリネーム不可
    $par_dir = dirname( $fpath );
    if( !is_readable($par_dir) || !is_writable($par_dir) ) return false;

    // ファイルが存在しなければ終了
    if( !file_exists( $fpath ) ) return false;

    if( is_dir( $fpath ) ) return is_writable( $fpath );

    return true;
}


/*
 * ディレクトリを削除する
 */
function delete_item( $fpath ){
    if( !file_exists( $fpath ) ){
        return false;
    }
    if( is_dir( $fpath ) ){ // ディレクトリの場合
        $files = array_diff( scandir( $fpath ), array('.','..') );
        foreach( $files as $file ){
            delete_item( "$fpath/$file" );
        }
        return rmdir( $fpath );
    } else {
        return unlink( $fpath );
    }

}

/*
 * ディレクトリトラバーサル用チェック
 */
function check_dir_traversal( $name ){
    if( strpos($name, '../') !== false ){
        return false;
    }
    return true;
}

/*
 * テンプレートの中身を取得する
 * シンボルテーブル上のインスタンスメソッドなどにアクセスされることを防ぐため、
 * 呼び出し元のクラスの外に定義するようにしている
 */
function get_template( $tmpl, $params=array() ){
    if( !empty($params) ){
        // シンボルテーブルに変数を展開
        extract( $params );
    }

    ob_start();
    if( (include "$tmpl") === false ) throw new Exception( "templete file include error ($tmpl)" );
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

/*
 * デバッグ環境かどうか
 */
function is_debug_env(){
    if( DEF('DEBUG') ){

        // IP判定
        $ip = DEF('DEBUG_IP');              // デバッグ許可IP
        $from_ip = $_SERVER['REMOTE_ADDR']; // リモートIP
        $ip_allowed = is_array($ip) ? in_array($from_ip, $ip) : $from_ip==$ip;

        // デバッグサーバ判定
        $host_reg = DEF('DEBUG_HOST_REGEX');
        $debug_server = preg_match("/$host_reg/", @$_SERVER['SERVER_NAME']) ||
            preg_match("/$host_reg/", @$_SERVER['HTTP_HOST']);

        return $ip_allowed || $debug_server;
    }
    return false;
}

/*
 * ajaxかどうかの判定
 */
function is_ajax(){
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}

/*
 * ログディレクトリ作成
 */
function log_setting(){
    if( DEF('PHP_LOG') ){
         $log_dir = DEF('LOG_DIR');
        if( !file_exists($log_dir) ){ // ログディレクトリ作成
            mkdir( $log_dir, 0777 );
        }
    }

    if( is_debug_env() ){// デバッグ環境
        ini_set('display_errors', 1);
        error_reporting( E_ALL | E_STRICT );
    }else{// 本番環境
        ini_set('display_errors', 0);
        error_reporting( E_ALL ^ E_NOTICE ^ E_DEPRECATED );
    }

    if( DEF('PHP_LOG') ){// PHPログ有効
        $log_file = sprintf( 'php_%s.log', date('Ymd') );
        ini_set( 'log_erros', 1 );
        ini_set( 'error_log', $log_dir.'/'.$log_file );
    }
}

/*
 * サイズの記述（50MB、100KBなど）をバイト数に変換する
 */
function size_to_byte( $size ){
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );

    $size = strtoupper( $size );
    $reg_unit = implode('|', $units);

    if( preg_match( "/^(([1-9]\d*|0)(\.\d+)?)\s*({$reg_unit}?)$/", $size, $m ) ){

        $size = $m[1];
        $unit = $m[4];

        $pos = array_search( $unit, $units );
        $size = floor( $size * pow(1024,$pos) );
        return $size;
    }

    return null;
}

/*
 * バイト数をサイズの記述（50MB、100KBなど）に変換する
 */
function byte_to_size( $byte ){
    if( $byte < 1024 ) return $byte.'B';
    $units = array( 'KB', 'MB', 'GB', 'TB', 'PB' );

    $len = $byte;
    while( count($units) ){
        $calc = $len / 1024;
        $u = array_shift( $units );

        if( $calc < 1024 || count($units)==0 ) return round($calc,1).$u;

        $len = $calc;
    }
}

function _d( $msg ){
    if( DEF('PHP_LOG') ){// PHPログ有効
        $log_file = DEF('LOG_DIR'). '/'. sprintf( 'php_%s.log', date('Ymd') );
        $msg = is_array( $msg ) ? print_r($msg, true) : $msg;
        file_put_contents( $log_file, $msg."\n", FILE_APPEND );
    }
}

/*
 * セッションを開始する
 */
function start_session(){
    $is_started = false;
    if( version_compare( phpversion(), '5.4.0', '>=' ) ){
        $is_started = session_status() === PHP_SESSION_ACTIVE ? true : false;
    } else {
        $is_started = session_id() === '' ? false : true;
    }

    if( !$is_started ){
        // セッション関連の設定をする
        $sess_ini = DEF('SESSION_INI');
        if( !empty($sess_ini) ){
            foreach( $sess_ini as $key => $val ){
                 ini_set( $key, $val );
            }
        }
        session_start();
    }
}

/*
 * CMS連携用のセッションデータ取得
 */
function get_cms_session_item($name){
    $a_name = strtolower($name);
    if (isset($_SESSION[DEF('SESSION_ROOT_NAME')][$name])) {
        return $_SESSION[DEF('SESSION_ROOT_NAME')][$name];
    } else if (isset($_SESSION[DEF('SESSION_ROOT_NAME')][$a_name])) {
        return $_SESSION[DEF('SESSION_ROOT_NAME')][$a_name];
    }
    return null;
}
/*
 * CMS連携用のセッションデータ記録
 */
function set_cms_session_item($name, $value){
    $name = strtolower($name);
    $_SESSION[DEF('SESSION_ROOT_NAME')][$name] = $value;
}

/*
 * ディレクトリのコピー
 */
function dir_copy($src_path, $dest_path) {
    if (!is_dir($dest_path)) { // コピー先がディレクトリでなければ作成する
        mkdir($dest_path);
    }

    if (is_dir($src_path)) { // コピー元がディレクトリ
        if ($dh = opendir($src_path)) {
            // コピー元ディレクトリを走査
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($src_path. '/'. $file)) {
                    dir_copy($src_path. '/'. $file, $dest_path. '/'. $file);
                } else {
                    copy($src_path. '/'. $file, $dest_path. '/'. $file);
                }
            }
            closedir($dh);
        }
    }
    return true;
}

/*
 * ディレクトリの削除
 */
function dir_delete($dir_path) {
    if (is_dir($dir_path)) { // ディレクトリ
        if ($dh = opendir($dir_path)) {
            // ディレクトリを走査
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($dir_path. '/'. $file)) {
                    // ディレクトリの場合は再帰処理
                    dir_delete($dir_path. '/'. $file);
                } else {
                    // ファイル
                    unlink($dir_path. '/'. $file);
                }
            }
            closedir($dh);
        }
        rmdir($dir_path);
    }

    return true;
}


/*
 * フラッシュメッセージの設定
 */
function flash_message( $msg=null ){
    start_session();

    if( empty( $msg ) ){ // フラッシュメッセージの取得
        $msg = !empty( $_SESSION['flash_message'] ) ? $_SESSION['flash_message'] : null;
        unset( $_SESSION['flash_message'] );
        return $msg;
    }else{ // フラッシュメッセージの保存
        $_SESSION['flash_message'] = $msg;
    }
    return null;
}

/*
 * 設定を取得するための関数
 */
function DEF( $key ){
    return NeuSettings::read( $key );
}
