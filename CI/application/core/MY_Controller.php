<?php

require_once(APPPATH.'core/Admin_Controller.php');

// PHP8.2対応
#[AllowDynamicProperties]
class MY_Controller extends CI_Controller
{
    const ENTITY_NAME = '';              // 扱うデータの名前（お知らせ、コラムなど）画面表示やログに使用される
    protected $_lang_files;         // 使用する言語ファイル（指定がなければ、 form/コントローラ名_lang.php というファイルがロードされる）

    // 下記は自動設定されます
    protected $_controller_name;    // コントローラー名
    protected $_method_name;        // メソッド名
    protected $_model;              // コントローラーのデフォルトモデル
    protected $_model_id;           // モデルID
    protected $_model_name;         // モデル名
    protected $_confirm_flag;       // 確認画面フラグ
    protected $_admin_mode = false; // 管理者モード=false

    public function __construct()
    {
        parent::__construct();

        $this->_controller_name = $this->router->class;
        $this->_method_name     = $this->router->method;

        // フォーム用言語ファイル読み込み
        if (empty($this->_lang_files)) { // 指定がなければコントローラ別の言語ファイルを読み込む
            $this->_lang_files = 'form/'. $this->_controller_name;
        }
        $this->lang->load($this->_lang_files);

        if (!empty($this->_model_name)){ // モデル名が設定されている
            // モデル名からモデルをロードする
            $this->_model = $this->_load_model($this->_model_name);
        } else if (empty($this->_model)) { // モデルの指定なし
            // コントローラ名から自動的にモデルをロードする

            if ($this->_admin_mode && file_exists(APPPATH.'models/admin/Admin_'.lcfirst($this->_controller_name).'_model.php')){
                // 管理画面でのアクセス、かつ管理機能向けモデルが存在する
                $this->_model = $this->_load_model('Admin_'.lcfirst($this->_controller_name).'_model');
            } else if (!$this->_admin_mode && file_exists(APPPATH.'models/'.ucfirst($this->_controller_name).'_model.php')) {
                // 管理画面でのアクセスではない、かつ一般向けモデルが存在する
                $this->_model = $this->_load_model(ucfirst($this->_controller_name).'_model');
            }
            if ($this->_model) $this->_model_name = get_class($this->_model); // モデル名
        }

        // データ名をモデルにも設定（ログ用）
        if ($this->_model){
            $this->_model->fill_entity_name($this::ENTITY_NAME);
        }

        // デバッグ用プロファイラの有効化 (管理画面ログイン時のみ有効)
        if (!empty($this->my_session->admin) && $this->config->item('enable_profiler') && !$this->input->is_ajax_request()) {
            $this->output->enable_profiler(true);
        }
    }

    // 一覧画面表示
    public function index()
    {
        $results = $this->_model->search(null, $pager, true);
        $data = [
            'results' => $results,
            'pager' => array_merge($pager, ['config'=>['base_url' => base_url(uri_string())]]),
        ];
        $this->load->view($this->_controller_name.'/index', ['data'=>$data]);
        return $data;
    }

    // 詳細画面表示
    public function detail($id)
    {
        $orig_mode = $this->_model->preview_mode;
        if ($this->my_session->allow_preview && $this->input->get('preview')) {
            // プレビュー許可ユーザ、かつプレビューモードでの表示
            $this->_model->preview_mode = true;
        }
        $data = $this->_model->get_by_id($id);
        if (empty($data)) {
            show_404();
        }
        $this->_model->preview_mode = $orig_mode;
        $this->load->view($this->_controller_name.'/detail', ['data'=>$data]);
        return $data;
    }

    // 確認フラグの設定
    public function set_confirm_flag($flag)
    {
        $this->_confirm_flag = $flag;
    }
    // 確認フラグの取得
    public function get_confirm_flag()
    {
        return $this->_confirm_flag;
        // return true;
    }

    public function get_model_id()
    {
        return $this->_model_id;
    }

    public function get_admin_mode()
    {
        return $this->_admin_mode;
    }

    // limitのリストを取得する
    public function get_search_limits()
    {
        if (!empty($this->_model)) {
            return $this->_model->get_search_limits();
        }
        return null;
    }

    // 配列値のrequiredバリデーション
    // 複数の値のうち、一つでも選択されていればvalid
    //
    // ※MY_Form_validation.phpに記載したいが、
    // 配列値を分解して再帰的にバリデーション処理を行う仕様のため、
    // 配列全体として処理を行いたい処理には合わないため
    // ここに記載する
    public function _required_any($val, $field)
    {
        $CI = get_instance();
        $comp_val = $this->form_validation->validation_data[$field];
        if ($data !== null ){
            if (is_array($data)) {
                $data = array_filter($data);
                if (count($data)>0) return true;
            } else {
                return true;
            }
        }

        return false;
    }

    // 条件付で必須チェックを行う
    public function _required_if($val, $param)
    {
        if (preg_match('/^(.+?)(!)?=(.+?)$/', $param, $m)) {
            $CI = get_instance();
            $comp_field = $m[1];
            $not = $m[2] ? true : false;
            $val_cond = $m[3];

            if ($val_cond=='null' || $val_cond=='empty') $val_cond = '';
            // $comp_val = $CI->input->post($comp_field);
            $comp_val = @$this->form_validation->validation_data[$comp_field];
            if (empty($val)) { // 入力がされていない
                if (($not && $comp_val != $val_cond) || (!$not && $comp_val == $val_cond)) return false;
            }
            return true;
        }
        return false;
    }


    // プレビュー用の認証処理
    protected function _preview_auth()
    {
        // ログイン済みの場合はその他無条件でＯＫ
        $user = $this->my_session->admin;
        if(!empty($user)) return true;

        $type = $this->config->item('PREVIEW_TYPE');
        if ($type==='user') { // プレビュータイプ＝ユーザ認証

        } else if ($type==='ip'){ // プレビュータイプ=IP判定
            $ips = $this->config->item('PREVIEW_ALLOW_IP');
            $remote_ip = $this->config->item('REMOTE_ADDR');
            if (in_array($remote_ip, $ips) ||
                $remote_ip==='127.0.0.1' ||
                $remote_ip==='::1' ||
                preg_match('/^'.preg_quote('192.168.').'\d{1,3}\.\d{1,3}$/', $remote_ip)) {
                return true;
            }
        } else if ($type==='auth') { // プレビュータイプ=ベーシック認証
            $user = $this->my_session->admin;
            if(!empty($user)) return true; // ログインユーザならベーシック認証は行わない

            // $_SERVER['HTTP_AUTHORIZATION']に認証データが入っているので取得する。
            // mod_rewriteにより、環境変数にREDIRECTというプレフィックスが設定されるため、
            // 正規表現で取得する
            // ※ここで認証データの取得ができない場合は
            // .htaccessにおいて環境変数HTTP_AUTHORIZATIONが設定されるように記述がされていない可能性あり
            $auth = null;
            foreach ($_SERVER as $key => $val) {
                if (preg_match('/HTTP_AUTHORIZATION$/', $key)) {
                    $auth = $val;
                    break;
                }
            }
            if (!empty($auth)) {
                // 認証文字列をユーザ名とパスワードに分解
                list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($auth, 6)));
            }

            $user = @$_SERVER['PHP_AUTH_USER'];
            $pw = @$_SERVER['PHP_AUTH_PW'];
            $validated = $user === $this->config->item('PREVIEW_BASIC_USER') &&
                         $pw === $this->config->item('PREVIEW_BASIC_PW');
            if ($validated) { // 認証成功
                return true;
            } else {
                // 認証エラー
                header('HTTP/1.1 401 Unauthorized');
                header('WWW-Authenticate: Basic realm="Basic Authentication"');
                echo 'ユーザ名およびパスワードを入力してください';
                exit();
            }
        }
        return false;
    }

    // CMS連携アップローダの設定
    // $data_id: データのID（ファイルはIDごとにサブディレクトリ直下に保存される）
    protected function _init_cms_uploader($data_id=null)
    {
        $uploader_model = $this->_model->get_uploader_model();
        if (CMS_UPLOADER_ENABLED && $uploader_model) {
            $this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.upload_mode'} = 'cms';
            $this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.cms_upload_subdir'} = $uploader_model;
            if ($data_id && preg_match('/^\d{1,10}$/', $data_id)) {
                $this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.cms_upload_data_id'} = $data_id;
            } else {
                unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.cms_upload_data_id'});
            }
            unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.new_id'});
        } else {
            unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.upload_dir'});
            unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.upload_mode'});
            unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.cms_upload_subdir'});
            unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.cms_upload_data_id'});
            unset($this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.new_id'});
        }
    }

    //
    // モデルを読み込んでインスタンスを返す
    // $name: モデル名
    protected function _load_model($name) {
        $basepath = $name;

        if (preg_match('/^Admin/', $name)) { // Admin
            $basepath = 'admin/'.$name;
        } else {
            $basepath = $name;
        }

        if (file_exists(APPPATH.'models/'.$basepath.'.php')) {
            $this->load->model($basepath);
        }
        return $this->{$name};
    }

    public function load_model($name){
        if(empty($name))return;
        if(is_array($name)){
            foreach($name as $_name){
                $basepath = '';
                if (preg_match('/^Admin/', $_name)) { // Admin
                    $basepath = 'admin/'.$_name;
                } else {
                    $basepath = $_name;
                }
                $this->load->model($basepath.'_model',$_name);
            }
        }
        else {
            $basepath = '';
            if (preg_match('/^Admin/', $name)) { // Admin
                $basepath = 'admin/'.$name;
            } else {
                $basepath = $name;
            }
            $this->load->model($basepath.'_model',$name);
        }
    }

    // 全てのVIEWキャッシュを削除する
    protected function _delete_all_cache () {
        $cache_path = $this->config->item('cache_path');
		if ($cache_path === '') {
			$cache_path = APPPATH.'cache/';
        }

        $files = scandir($cache_path);
        foreach ($files as $key => $val) {
            $path = $cache_path. '/'. $val;
            if (in_array($val, ['.', '..']) || is_dir($path)) continue;
            unlink($path);
        }
    }

    // リアルタイムプレビュー表示
    public function preview_changes()
    {
        if ( empty($_POST) || $this->my_session->admin == false) {
            show_404();
        }
        $this->_model->preview_mode = true;
        $data = $_POST;
        $this->load->view($this->_controller_name.'/detail', ['data'=>$data]);
    }

    // プレビュー
    public function preview_page($id_md5)
    {
        $result = $this->_model->get_data_for_preview($id_md5);
        if( empty($result) ) show_404();
        $data = $result;
        $this->load->view($this->_controller_name.'/detail', compact('data'));
    }
}
