<?php
class Fileloader extends MY_Controller {

    // ファイルのリード処理
    // .htaccessにて下記の設定を追加すること
    // RewriteCond %{REQUEST_URI} ^[アップロードディレクトリ]
    // RewriteRule ^ ci.php [QSA,E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
    public function loader()
    {
        if (func_num_args() < 3) show_404();

        $args = func_get_args();
        $args = array_splice($args, -3, 3);

        list($model,$id,$file) = $args;

        // 指定されたモデル名のチェックおよび読み込み
        $load_model = ucfirst($model).'_model';
        if (file_exists(APPPATH.'models/'.$load_model.'.php')) {
            $this->load->model($load_model);
        }

        $preview_passed = false;
        if (!empty($_SERVER['HTTP_REFERER']) && preg_match('/(\?|\&)preview=1/', $_SERVER['HTTP_REFERER'])) {
            // プレビュー経由でのファイルアクセス
            $preview_passed = $this->_preview_auth(); // 認証を実行
        }

        // log_message('debug', print_r(['admin'=>!empty($this->my_session->admin), 'preview'=>$preview_passed, 'publish'=>!empty($this->$load_model->get_by_id($id))],true));

        $permitted = false;
        if ($this->my_session->admin ||
            $preview_passed ||
            !empty($this->$load_model->get_by_id($id))) {
            // 下記条件のいずれかを満たせばアクセスOK
            // ・管理者としてログインしている
            // ・プレビュー経由でのアクセスの場合、プレビューの認証済み
            // ・指定のデータが公開済みである
            $permitted = true;
        }

        // log_message('debug', print_r(['file'=>$file, 'permitted'=>$permitted],true));

        if (!$permitted) {
            // アクセス拒否
            show_404();
        }

        $dir = DOCUMENT_ROOT.base_url(CMS_UPLOADER_UPLOAD_DIR);
        // ファイルを読み込む
        $path = $dir."/{$model}/{$id}/$file";

        if (!file_exists($path) || !is_file($path)) {
            show_404();
        }

        $mod_time = filemtime($path);
        $last_modified = gmdate('D, d M Y H:i:s T', $mod_time);
        $original_etag = md5_file($path);
        $mime = mime_content_type($path);

        // log_message('debug', 'Expires:'. gmdate('D, d M Y H:i:s T', time() + $expire));

        header('Cache-Control: max-age='.UPLOADER_FILE_EXPIRES);
        header('Pragma: cache');
        header('Expires: '. gmdate('D, d M Y H:i:s T', time() + UPLOADER_FILE_EXPIRES));
        header('ETag: "'. $original_etag.'"');

        $this->_respond_not_modified($mod_time, $original_etag);

        header('Last-Modified: '. $last_modified);
        header('Content-Type: '. $mime);
        readfile($path);
    }

    // IF_MODIFIED_SINCEヘッダをタイムスタンプで取得する
    private function _get_if_modified_since()
    {
        // log_message('debug', "HTTP_IF_MODIFIED_SINCE:".@$_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $mod = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            if (strpos($mod, 'GMT') === false) {
                $mod .= ' GMT';
            }
            $since = strtotime($mod);
            if ($since !== false) {
                return $since;
            }
        }
        return 0;
    }

    // IF_NONE_MATCH(ETag)ヘッダを取得する
    private function _get_if_none_match()
    {
        // log_message('debug', "HTTP_IF_NONE_MATCH:".@$_SERVER['HTTP_IF_NONE_MATCH']);
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            return preg_replace('/^"+|"+$/', '', $_SERVER['HTTP_IF_NONE_MATCH']);
        }
        return '';
    }

    // 必要があれば304 Not Modifiedヘッダを返す
    private function _respond_not_modified($modified_time=false, $original_etag=false)
    {
        $since = $this->_get_if_modified_since();
        $etag = $this->_get_if_none_match();
        // log_message('debug', 'since:'. gmdate('D, d M Y H:i:s T', $since). ' modified:'. gmdate('D, d M Y H:i:s T',$modified_time));
        // log_message('debug', 'etag:'. $etag. ' original_etag:'. $original_etag);
        if (($modified_time && is_numeric($modified_time) && $since >= $modified_time) &&
            ($original_etag && $etag === $original_etag)) {
            // log_message('debug', '304 Not Modified');
            header('HTTP/1.1 304 Not Modified');
            exit(0);
        }
    }
}
