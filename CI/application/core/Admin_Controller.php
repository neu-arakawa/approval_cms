<?php

class Admin_Controller extends MY_Controller
{
    const VIEW_ICON = null;                             // アイコン

    protected $_validate_in_search = true;              // 検索処理時にデータ個別のバリデーションを行うか（プレビュー用)
    protected $_allowed_methods = null;                 // 未ログインでもアクセスを許可するメソッド名
    protected $_admin_mode = true;                      // 管理者モード=true
    protected $_exec_methods = [                        // 基本操作系メソッドの中で実行を許可するメソッド(この設定はAdminControllerに定義されていないメソッドには影響を与えない)
        // 'index', 'add', 'edit',
        // 'delete', 'delete_all',
        // 'replicate', 'replicate_all',
        // 'publish', 'publish_all',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['admin_form']);
        $this->lang->load('form/admin_common');     // フォーム用言語ファイル読み込み(共通)

        // ログインチェック
        if (!$this->my_session->admin) {
            // 許可されたメソッド以外はログイン必須
            $allowed = false;
            if (is_array($this->_allowed_methods) && in_array($this->_method_name, $this->_allowed_methods)) $allowed = true;

            if (!$allowed) {
                $this->my_session->admin_request_url = uri_string(); // アクセス元のURL記録
                redirect('/'.ADMIN_DIR_NAME.'/login');
            }

        } else { // ログイン済み
            // アップローダー用の認証フラグON
            $this->my_session->{UPLOADER_SESSION_ROOT_NAME.'.uploader_auth'} = true;
        }

        if (method_exists(__CLASS__, $this->_method_name)) {
            // AdminControllerに定義されているメソッドが呼び出された
            if (!in_array($this->_method_name, $this->_exec_methods)) {
                // 実行可能メソッドとして定義されていない場合はアクセスを許可しない
                show_404();
            }
        }

    }

    // // 共通アクション定義 ここから ///////////////////////////////////////////////////////////

    // 一覧表示アクション
    public function index()
    {
        $data = $this->_call_action_method('_index');

        // コントローラ別のテンプレートが存在する場合、
        // そちらを優先して使用する。(例：admin/column/index)
        // 存在しない場合は共通のテンプレート(admin/common/index)を使用する
        // 共通テンプレートからは、各コントローラ別のフォームテンプレートをインクルードする
        // 例：admin/column/common/index.php
        $view = 'admin/'.$this->_controller_name.'/index';
        $index_path = '';
        if (!file_exists(VIEWPATH. $view.'.php')) { // 個別のテンプレートが存在しない
            $index_path = VIEWPATH. 'admin/'. $this->_controller_name. '/includes/index.php';
            $view = 'admin/common/index';
        }
        $this->load->view($view, ['data'=>$data, 'index_path'=>$index_path]);
    }

    // // 一覧表示アクション
    // public function index_modal()
    // {
    //     $this->layout = 'admin_modal';
    //     $this->Prg->commonProcess();

    //     // 検索値をdataに設定（フォームに反映されるように）
    //     $this->request->data[$this->modelClass] = $this->request->query;
    //     $request = $this->request->data;

    //     // searchプラグイン設定
    //     $cond = $this->model->parseCriteria( $request[$this->modelClass] );

    //     // ページングの検索条件を取得
    //     $this->paginate = $this->model->getFindOptions(['conditions'=>$cond]);

    //     // unset($this->request->params['named']['sort']);
    //     // unset($this->request->params['named']['direction']);

    //     $this->set('data', $this->paginate());
    // }

    // 新規追加アクション
    public function add()
    {
        // 編集アクションに転送
        admin_redirect($this->_controller_name.'/edit');
    }

    // 編集アクション
    public function edit($id=null)
    {
        // 画面遷移処理開始
        $this->_transition($id);
    }

    // 削除アクション
    public function delete($id)
    {
        $this->_delete($id);
        admin_redirect($this->_controller_name.'/index');
    }

    // 一括削除アクション
    public function delete_all()
    {
        $this->_delete_all();
        admin_redirect($this->_controller_name.'/index');
    }

    // 複製アクション
    public function replicate($id)
    {
        if ($save_id=$this->_replicate($id)) {
            admin_redirect($this->_controller_name.'/edit/'.$save_id); // 編集画面へリダイレクト
        } else {
            admin_redirect($this->_controller_name.'/index'); // 一覧画面をリダイレクト
        }
    }

    // 一括複製アクション
    public function replicate_all()
    {
        $this->_replicate_all();
        // $this->load->view('admin/common/index');
        admin_redirect($this->_controller_name.'/index');
    }

    // 公開/非公開アクション
    public function publish($id, $flg)
    {
        $this->_publish($id, $flg);
        admin_redirect($this->_controller_name.'/index');
    }

    // 一括公開/非公開アクション
    public function publish_all($flg)
    {
        $this->_publish_all($flg);
        admin_redirect($this->_controller_name.'/index');
    }

    // // 詳細画面アクション
    // public function detail($id)
    // {
    //     $orig_mode = $this->model->previewMode;
    //     if ($this->Session->read('allow_preview') && !empty($this->request->named['preview'])) {
    //         // プレビュー許可ユーザ、かつプレビューモードでの表示
    //         $this->model->previewMode = true;
    //     }
    //     $data = $this->model->getByID($id);
    //     $this->model->previewMode = $orig_mode;

    //     if (empty($data)) {
    //         throw new NotFoundException();
    //     }
    //     $this->set('data', $data);
    //     return $data;
    // }

    // 共通アクション定義 ここまで -------------------------------------------------------

    // 画面遷移処理から呼ばれるコールバック ここから /////////////////////////////////////////
    // ※必要であれば、コントローラにてオーバーライドして処理を追加する

    // 入力時コールバック
    protected function _input()
    {
        // 編集対象データを取得して返す
        // $this->modelIDは対象データのID.
        // _transition関数呼び出し時にセットされる
        if (!empty($this->_model_id)) {
            return $this->_model->get_by_id($this->_model_id);
        }
        return null;
    }

    // キャンセル時コールバック
    protected function _cancel()
    {
        // 何かキャンセルの際に必要な処理
        $this->flash->info('編集処理をキャンセルしました');
    }

    // フォームバック時コールバック
    protected function _back()
    {
        // 何かフォームバックの際に必要な処理
    }

    // フォームエラーバック時コールバック
    protected function _error_back()
    {
        // 何かエラーバックの際に必要な処理
    }

    // 下書き保存時コールバック(編集画面経由)
    protected function _draft($data)
    {
        // 下書き保存処理
        $data['status'] = APPROVAL_STATUS_DRAFT;
        $save_id = $this->_model->save($data, $this->_model_id, false);
        if ($save_id) { // 保存成功
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '下書き保存', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('ID:%s「%s」の下書き保存が完了しました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '下書き保存', false, "下書き対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の下書き保存に失敗しました', $this->_model_id)
            );
        }
    }

    // 確認時コールバック
    protected function _confirm($data)
    {
        // 何か確認画面表示時に必要な処理
    }

    // 保存処理時コールバック
    // $data: 保存対象データ
    protected function _complete($data)
    {
        // 保存処理
        $save_id = $this->_model->save($data, $this->_model_id, true);
        if ($save_id) { // 保存成功
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '公開保存', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $edit_btn = '<a href="'.admin_base_url($this->_controller_name.'/edit').'" type="button" class="btn btn-primary btn-xs">再度編集する</a>';
            $this->flash->info(
                sprintf('ID:%s「%s」の保存が完了しました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '公開保存', false, "公開保存対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の保存に失敗しました', $this->_model_id)
            );
        }
    }

    // 一覧表示時コールバック
    protected function _index()
    {
        $search_key = 'search'.md5($this->_controller_name.$this->_method_name);
        if( !empty($_GET) ){
            unset($this->my_session->{$search_key});
            $this->my_session->{$search_key} = $_GET;
        }
        else if(!empty($this->my_session->{$search_key}) ) {
            $_GET = $this->my_session->{$search_key};
        } 
        $results = $this->_model->search(null, $pager, true);
        if ($this->_validate_in_search) { // 検索時のバリデーション有効
            if (!empty($results)) {
                foreach ($results as $idx => $arr) {
                    // バリデーション実行
                    $results[$idx]['validated'] = $this->_exec_validate($arr);
                }
                // リセット
                $this->form_validation = new CI_Form_validation();
            }
        }
        $data = [
            'results' => $results,
            'pager' => array_merge($pager, ['config'=>['base_url'=>base_url(uri_string())]]),
        ];
        return $data;
    }

    // 削除時コールバック
    protected function _delete($id) {
        $del_id = $this->_model->delete($id);
        if ($del_id) { // 削除成功
            // 操作ログ保存
            $this->_save_ope_log(
                $del_id, '削除', true, "削除時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('ID:%s「%s」の削除が完了しました', $del_id, strip_tags($this->_model->get_last_title()) )
            );
            return true;
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $del_id, '削除', false, "削除時のID:".$id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の削除に失敗しました', $id)
            );
            return false;
        }
    }

    // 複製時コールバック
    protected function _replicate($id) {

        $save_id = $this->_model->replicate($id);
        if ($save_id) { // 複製成功
            // 操作ログ保存
            $this->_save_ope_log(
                $id, '複製', true, ['複製元ID:'.$id, '複製後ID:'. $save_id, '複製後タイトル:'.strip_tags($this->_model->get_last_title())]
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('データを複製してID:%s「%s」として保存しました', $save_id, strip_tags($this->_model->get_last_title()))
            );
            return $save_id;
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $id, '複製', false, '複製元ID:'. $id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の複製に失敗しました', $id)
            );
            return false;
        }
    }

    // 公開／下書き処理時コールバック
    protected function _publish($id, $flg)
    {
        if ($flg) { // 公開処理
            // 完全なデータのみ公開対象とするため、妥当性をチェックする
            // (下書き状態の未完全データを想定)
            $data = $this->_model->get_by_id($id);
            $validated = $this->_exec_validate($data); // editアクションのバリデート実行
            if (!$validated) { // 検証失敗
                $this->flash->error(
                    sprintf('ID:%s の公開に失敗しました。入力項目が全て揃っているかご確認ください', $id)
                );
                return false;
            }
        }

        // 公開処理
        $save_id = $this->_model->publish($id,$flg);
        $action = $flg ? '公開' : '非公開';
        if ($save_id) { // 公開/非公開成功
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, $action, true, "{$action}時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('id:%s「%s」の%sが完了しました', $save_id, strip_tags($this->_model->get_last_title()), $action)
            );
            return true;
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, $action, false, "{$action}対象ID:". $id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の%sに失敗しました', $id, $action)
            );
            return false;
        }
    }

    // 一括複製時コールバック
    protected function _replicate_all() {
        $ids = $this->input->get('ids');
        $ret = $this->_model->replicate_all($ids);
        if ($ret) { // 複製成功
            // 操作ログ保存
            $this->_save_ope_log(
                null, '一括複製', true, "複製対象ID:". implode(',',$this->_model->get_last_data())
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('一括複製が完了しました')
            );
        } else { // 削除失敗
            // 操作ログ保存
            $this->_save_ope_log(
                null, '一括複製', false, "複製対象ID:". $ids
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('一括複製に失敗しました')
            );
        }
    }

    // 一括削除時コールバック
    protected function _delete_all()
    {
        $ids = $this->input->get('ids');
        $ret = $this->_model->delete_all($ids);
        if ($ret) { // 削除成功
            // 操作ログ保存
            $this->_save_ope_log(
                null, '一括削除', true, "削除対象ID:". implode(',',$this->_model->get_last_data())
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('一括削除が完了しました')
            );
        } else { // 削除失敗
            // 操作ログ保存
            $this->_save_ope_log(
                null, '一括削除', false, "削除対象ID:". $ids
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('一括削除に失敗しました')
            );
        }
    }

    // 一括公開／下書き処理コールバック
    protected function _publish_all($flg)
    {
        $ids = $this->input->get('ids');
        $ids = explode(',', $ids);
        $save_ids = $ids;
        $err_ids = [];
        if (!empty($ids)) {
            if ($flg) { // 公開処理

                // 一つずつ、入力が揃っているか判定するためバリデート処理を行う
                foreach ($ids as $idx => $id) {
                    $data = $this->_model->get_by_id($id);
                    $validated = $this->_exec_validate($data); // editアクションのバリデート実行
                    if (!$validated) { // 検証失敗
                        unset($save_ids[$idx]); // 保存対象から外す
                        $err_ids[] = $id;
                    }
                }
            }
        }

        $ret = $this->_model->publish_all($save_ids, $flg);
        $action = $flg ? '公開' : '下書き';
        if ($ret) { // 処理成功

            $sub_msg = '';
            $flash_msg = '';
            if (!empty($err_ids)) { // 入力が未完で公開できないものがある
                $log_msg = "{$action}対象ID:". implode(',',$this->_model->get_last_data()). " {$action}できなかったID:". implode(',', $err_ids);
                $flash_msg = "一括{$action}が完了しました。一部入力が完了していないため{$action}できないデータがありました。{$action}できなかったIDは下記です。\n". implode(' / ', $err_ids);
            } else {
                $log_msg = "{$action}対象ID:". implode(',',$this->_model->get_last_data());
                $flash_msg = "一括{$action}が完了しました。";
            }
            // 操作ログ保存
            $this->_save_ope_log(
                null, "一括{$action}", true, $log_msg
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                $flash_msg
            );
        } else { // 処理失敗
             // 操作ログ保存
            $this->_save_ope_log(
                null, "一括{$action}", false, "{$action}対象ID:". $this->input->get('ids')
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('一括公開に失敗しました')
            );
        }
    }

    // 下書き用のバリデーション処理
    // 必要があれば、コントローラでオーバーライドし、
    // バリデーション結果を返す
    // ※オーバーライドすると、自動的にそのバリデート結果を優先するので注意
    // public function _validate_draft()
    // {
    //     return true;
    // }

    // 通常バリデーション処理
    // 必要があれば、コントローラでオーバーライドし、
    // バリデーション結果を返す
    // ※オーバーライドすると、自動的にそのバリデート結果を優先するので注意
    // public function _validate()
    // {
    //     return true;
    // }

    // // ワークフロー　取り下げ時コールバック
    // public function _wf_deny()
    // {
    //     var_dump("wf_deny");
    // }

    // // ワークフロー　承認依頼時コールバック
    // public function _wf_request()
    // {
    //     var_dump("wf_request");
    // }

    // // ワークフロー　公開時コールバック
    // public function _wf_publish()
    // {
    //     var_dump("wf_publish");
    // }

    // 画面遷移用処理の記述 ここから ///////////////////////////////////////////////////////////

    // 画面遷移用のセッションキー定義
    // 編集画面の場合はIDを付加することで複数画面の起動が可
    protected function _trans_session_key()
    {
        if( !empty($this->_model_id) ) return $this->_model_name.'.'.$this->_method_name.'.'.$this->_model_id;
        else return $this->_model_name.'.'.$this->_method_name. '.new';
    }

    // バリデート済みかどうかのフラグ設定
    protected function _set_validated($value)
    {
        $sess_key = $this->_trans_session_key().'.validated';
        if ($value) $value = true;
        else $value = false;
        $this->my_session->{$sess_key} = $value;
    }
    // バリデート済みかどうか
    protected function _is_validated(){
        $sess_key = $this->_trans_session_key().'.validated';
        return $this->my_session->{$sess_key} ? true : false;
    }

    // 一時データを保存する
    // $data: 保存するデータ
    // $field: 特定のフィールドにデータを保存する場合に指定する
    // ※ $fieldはモデル.フィールド名の形式で指定すること
    protected function _save_temp_data($data, $field=null){
        if ($field) $sess_key = $this->_trans_session_key().'.'.$field;
        else $sess_key = $this->_trans_session_key();
        $this->my_session->{$sess_key} = $data;
    }
    // 一時データを取得する
    // $field: 特定のフィールドのデータを取得する場合に指定
    protected function _get_temp_data($field=null){
        if ($field) $sess_key = $this->_trans_session_key().'.'.$field;
        else $sess_key = $this->_trans_session_key();
        return $this->my_session->{$sess_key};
    }
    // セッションに保存した一時テータの初期化
    // $field: 特定のフィールドのデータのみを初期化する場合に指定
    protected function _init_temp_data($field=null){
        if ($field) $sess_key = $this->_trans_session_key().'.'.$field;
        else $sess_key = $this->_trans_session_key();
        unset($this->my_session->{$sess_key});
    }

    // 子コントローラに定義したアクション用メソッドの呼び出し
    // $method: メソッド名
    protected function _call_action_method($method)
    {
        if (method_exists($this, $method)) {
            $args = func_get_args();
            array_shift($args);
            return call_user_func_array([$this, $method], $args);
        }
        return null;
    }

    // 画面遷移処理
    // 必要に応じて、アクションメソッド、およびコールバックメソッドが呼び出される
    // $id: 編集対象のID（新規登録時はnull）
    // $validate_group: バリデーションのグループ名(指定しなければ admin/[controller名]/edit )
    protected function _transition($id=null, $validate_group=null){
        // デフォルトのテンプレート
        $action = 'edit';

        $this->_model_id = $id;
        if( !empty($this->_model_id) ){
            $this->_current_date = $this->_model->get_by_id($this->_model_id);
            $this->load->vars(['current_data'=>$this->_current_date]);
        }
        if ($this->input->method()=='post') {
            // データがPOST送信された
            if ($this->input->post('btn_direct')!==null) {
                $validated = $this->_exec_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    if (!is_dev_env()) $this->_init_temp_data(); // 開発環境以外はリロードを防止するために入力データをクリア
                    $this->_call_action_method('_complete', $this->input->post());    // 保存処理メソッド呼び出し。
                    admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                } else { // 検証失敗
                    $this->_set_validated(false);
                    $this->_call_action_method('_error_back');
                }
            } else if ($this->input->post('btn_cancel')!==null) {
                // キャンセルボタンが押された
                $this->_init_temp_data(); // 保存済みデータを破棄
                $this->_call_action_method('_cancel');
                admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                $action = 'cancel';
            } else if($this->input->post('btn_back')!==null) {
                // 戻るボタンが押された
                $this->_data2post($this->_get_temp_data()); // 保存データをPOST値に復元
                $this->_set_validated(false); // 確認済みフラグをOFF
                $this->_call_action_method('_back');
            } else if($this->input->post('btn_draft')!==null){
                // 下書き保存ボタンが押された
                // 下書き用バリデート処理実行
                $validated = $this->_exec_draft_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    $this->_init_temp_data();
                    $this->_call_action_method('_draft', $this->input->post());
                    admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                    $action = 'draft';
                } else { // 検証失敗
                    $this->_call_action_method('_error_back');
                }
            } else if($this->input->post('btn_pending')!==null){
                // 承認依頼
                // バリデート処理実行
                $validated = $this->_exec_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    $this->_call_action_method('_pending', $this->input->post());
                    admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                } else { // 検証失敗
                    $this->_call_action_method('_error_back');
                }

            } else if($this->input->post('btn_pending_cancel')!==null){
                // 承認依頼
                // バリデート処理実行
                $validated = $this->_exec_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    $this->_call_action_method('_pending_cancel', $this->input->post());
                    admin_redirect($this->_controller_name.'/edit/'.$this->_model_id);   // 一覧にリダイレクト
                } else { // 検証失敗
                    $this->_call_action_method('_error_back');
                }

            } else if($this->input->post('btn_pending_while')!==null){
                // 承認依頼
                // バリデート処理実行
                $validated = $this->_exec_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    $this->_call_action_method('_pending_draft', $this->input->post());
                    admin_redirect($this->_controller_name.'/edit/'.$this->_model_id);   // 一覧にリダイレクト
                } else { // 検証失敗
                    $this->_call_action_method('_error_back');
                }

            } else if($this->input->post('btn_reject')!==null){
                // 承認依頼

                // バリデート処理実行
                $validated = $this->_exec_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    $this->_call_action_method('_reject', $this->input->post());
                    admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                } else { // 検証失敗
                    $this->_call_action_method('_error_back');
                }

            } else if($this->input->post('btn_approved')!==null){
                // 承認OK

                // バリデート処理実行
                $validated = $this->_exec_validate(null, $validate_group);
                if ($validated) { // 検証成功
                    $this->_call_action_method('_approved', $this->input->post());
                    admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                } else { // 検証失敗
                    $this->_call_action_method('_error_back');
                }

            } else {
                // その他送信ボタンが押された

                if ($this->_is_validated()) { // データが検証済み
                    $save_data = $this->_get_temp_data();

                    if ($this->input->post('btn_deny')!==null) {
                        // ワークフロー 取り下げボタンが押された
                        $this->_call_action_method('_wf_deny');
                        $this->_init_temp_data();
                        return;
                    } else if ($this->input->post('btn_request')!==null) {
                        // ワークフロー　承認依頼ボタンが押された
                        $this->_call_action_method('_wf_request');
                        $this->_init_temp_data();
                        return;
                    } else if ($this->input->post('btn_publish')!==null) {
                        // ワークフロー　公開ボタンが押された
                        $this->_call_action_method('_wf_publish');
                        $this->_init_temp_data();
                        return;
                    } else if ($this->input->post('btn_confirm')!==null) {
                        // 確認ボタンが押された(リロード等)
                        $this->_call_action_method('_confirm', $save_data);
                        $action = 'confirm';    // 確認テンプレート使用
                    } else {
                        // 確認画面からの送信を想定
                        if (!is_dev_env()) $this->_init_temp_data(); // 開発環境以外はリロードを防止するために入力データをクリア
                        $this->_call_action_method('_complete', $save_data);    // 保存処理メソッド呼び出し。
                        // exit();
                        admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
                        $action = 'complete';
                    }

                } else { // データ未検証
                    // バリデート処理実行
                    $validated = $this->_exec_validate(null, $validate_group);
                    if ($validated) { // 検証成功
                        $this->_save_temp_data($this->input->post());  // 入力データ保存
                        $this->_set_validated(true);                  // バリデート済みフラグ
                        $this->_call_action_method('_confirm', $this->input->post());
                        $action = 'confirm'; // 確認テンプレートを使用
                    } else { // 検証失敗
                        $this->_set_validated(false);
                        $this->_call_action_method('_error_back');
                    }
                }
            }


        } else {
            // データ送信なし
            // 初回の画面表示

            // 一時データをクリア
            $this->_init_temp_data();
            // CMS連携アップローダの設定
            $this->_init_cms_uploader($id);

            $data = $this->_call_action_method('_input');  // コールバックを呼び出し、編集用データを取得
            if (!empty($id) && empty($data)) {
                // IDが指定されているが、データの取得ができなかった場合
                show_404();
            }

            // データをPOST値に反映
            $this->_data2post($data);
        }

        // コントローラ別のテンプレートが存在する場合、
        // そちらを優先して使用する。(例：admin/column/edit)
        // 存在しない場合は共通のテンプレート(admin/common/edit or confirm)を使用する
        // 共通テンプレートからは、各コントローラ別のフォームテンプレートをインクルードする
        // 例：admin/column/common/form.php
        $view = 'admin/'.$this->_controller_name.'/'. $action;
        $form_path = '';
        if (!file_exists(VIEWPATH. $view.'.php')) { // 個別のテンプレートが存在しない
            $form_path = VIEWPATH. 'admin/'. $this->_controller_name. '/includes/form.php';
            $view = 'admin/common/'. $action;
        }
        $this->load->view($view, ['form_path'=>$form_path, 'action'=>$action, 'model_id'=>$this->_model_id]);
    }
    // 画面遷移用処理の記述 ここまで -------------------------------------------------------

    // 操作ログの保存
    // $id: 操作対象のデータID
    // $action: 操作名 例) 削除、公開など
    // $success: 処理結果(1:成功 0:失敗)
    // $desc: 詳細記述
    // $login_name: ログイン名（ログイン失敗時の場合はセッションからユーザ情報を取得できないため代わりに使用する）
    public function _save_ope_log($id, $action, $success, $desc, $login_name=null)
    {
        if (empty($this->Admin_log_model)) {
            try {
                // ログ保存用のモデルをロード
                $this->load->model('admin/Admin_log_model');
            } catch (Exception $e) { // ロード失敗
                return false;
            }
        }

        $user = $this->my_session->userdata('admin');
        if (empty($user)) { // 認証済みユーザデータの取得失敗
            // ユーザIDは空、ログイン名は指定値を使用する
            $user_id = null;
        } else { // 認証済みユーザデータの取得成功
            // 認証済みユーザデータのID、ログイン名を使用する
            $user_id = $user->id;
            $login_name = $user->{AUTH_USER_FIELD};
        }

        // 保存用データの作成
        if (!empty($desc) && is_array($desc)) $desc = implode("\n", $desc);
        $save = [
            'user_id' => $user_id,
            'login_name' => $login_name,
            'class_name' => $this->_controller_name,
            'method_name' => $this->_method_name,
            'data_id' => $id,
            'entity_name' => $this::ENTITY_NAME,
            'action' => $action,
            'description' => $desc,
            'flg_success' => $success ? 1 : 0,
            'remote_ip' => $this->config->item('REMOTE_ADDR'),
            'remote_ua' => !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
        ];
        // 保存実行
        return $this->Admin_log_model->save($save);
    }

    // バリデーションの実行
    // $data: バリデート対象のデータ
    // $config: バリデートグループ名
    protected function _exec_validate($data=null, $config=null)
    {
        if (!empty($data['id'])) {
            $this->_model_id = $data['id'];
        }
        if (method_exists($this, '_validate')) {
            // ユーザ定義のバリデート関数が定義されている場合
            // メソッドを実行してバリデート結果とする
            $validated = $this->_call_action_method('_validate', $data);
        } else {
            // ユーザ定義のバリデートメソッドが定義されていない場合
            // 通常のバリデートを実行
            $this->form_validation->reset_validation();
            if (empty($data)) { // データの指定なしの場合、POSTを使用する
                $data = $_POST;
            }
            $this->form_validation->set_data($data);
            if (empty($config)) {
                // デフォルトで edit をバリデーショングループとする
                $config = 'admin/'.$this->_controller_name.'/edit'; // バリデーショングループ
            }
            $this->before_validate($config, $data);
            $validated = $this->form_validation->run($config);
        }
        return $validated;
    }

    // 下書き用バリデーションの実行
    // $data: バリデート対象のデータ
    // $config: バリデートグループ名
    protected function _exec_draft_validate($data=null, $config=null)
    {
        if (method_exists($this, '_validate_draft')) {
            // ユーザ定義のバリデートメソッドが定義されている場合は
            // メソッドを実行してバリデート結果とする
            $validated = $this->_call_action_method('_validate_draft', $data);
        } else {
            // ユーザ定義のバリデートメソッドが定義されていない場合
            // 通常のバリデートを実行(必須等のルールは除外)
            $this->form_validation->reset_validation();
            if (empty($data)) { // データの指定なしの場合、POSTを使用する
                $data = $_POST;
            }
            $this->form_validation->set_data($data);
            if (empty($config)) {
                // デフォルトで edit をバリデーショングループとする
                $config = 'admin/'.$this->_controller_name.'/edit'; // バリデーショングループ
            }
            $this->before_validate($config, $data);
            $validated = $this->form_validation->run($config, $data, true);
        }
        return $validated;
    }

    // 保存データからPOST値への変換
    // $data: 変換したいデータ配列
    // $init_post: 現在のPOSTを初期化してから設定する場合はtrue
    protected function _data2post($data, $init_post=true)
    {
        if ($init_post) $_POST = [];
        if (!empty($data) && (is_object($data) || is_array($data)) ) {
            foreach ($data as $k => $v) {
                if (!preg_match('/^password/', $k)) {
                    $_POST[$k] = $v;
                }
            }
        }
    }

    // アクセス禁止画面
    protected function _forbidden()
    {
        $this->flash->error('アクセス権がありません');
        admin_redirect('/');
    }

    // バリデーション処理前
    // ルールの動的追加など
    protected function before_validate($config,$data=null) {
    }

    // 並び順の保存処理
    public function sort ()
    {
        if (!empty($_GET['ids'])) {
            // 並び順の送信あり
            if ($this->_model->save_sorted($_GET['ids'])) {
                // 保存成功
                $this->_save_ope_log(
                    null, '並び順保存', true, sprintf('並び順:%s', $_GET['ids'])
                );
                // フラッシュメッセージ設定
                $this->flash->info(
                    sprintf('並び順の保存が完了しました')
                );
            } else {
                // 保存失敗
                $this->_save_ope_log(
                    null, '並び順保存', false, sprintf('並び順:%s', $_GET['ids'])
                );
                $this->flash->error(
                    sprintf('並び順の保存に失敗しました')
                );
            }
            admin_redirect('/'.$this->_controller_name.'/sort');
        } else {
            // ソート済み一覧を取得
            $sorted = $this->_model->get_sorted();

            $view = 'admin/'.$this->_controller_name.'/sort';
            if (!file_exists(VIEWPATH. $view.'.php')) { // 個別のテンプレートが存在しない
                $view = 'admin/common/sort';
            }
            $this->load->view($view, compact('sorted'));
        }
    }

    // 保存処理時コールバック
    // $data: 保存対象データ
    protected function _pending($data)
    {
        if( $this->session->admin->flg_approval ) show_404();

        // 保存処理
        $data['status'] = APPROVAL_STATUS_PENDING;
        $data['pending_by'] = $this->my_session->admin->id;
        $data['pending_date'] = NOW;
        $save_id = $this->_model->save($data, $this->_model_id, true);
        if ($save_id) { // 保存成功
        	$to = $this->_model->get_approver_emails();
            if( !empty($to) && ENABLE_NOTIFY_APPROVAL ){
                // $this->load->library('email');
                // $this->email
                //     ->template('approval', [
                //         'entity_name'   => $this::ENTITY_NAME,
                //         'post_status'   => '承認依頼',
                //         'post_title'    => $data['title'],
                //         'post_edit_url' => admin_base_url($this->_controller_name.'/edit/'. $save_id, null, true),
                //         'user'          => $this->my_session->admin,
                //         'message'       => !empty($data['note'])? $data['note']:'(ありません)',
                //     ])
                //     ->to($to)
                //     ->send(false);
            }

            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認依頼', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );

            // フラッシュメッセージ設定
            $edit_btn = '<a href="'.admin_base_url($this->_controller_name.'/edit').'" type="button" class="btn btn-primary btn-xs">再度編集する</a>';
            $this->flash->info(
                sprintf('ID:%s「%s」の承認依頼しました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認依頼', false, "公開保存対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の承認依頼に失敗しました', $this->_model_id)
            );
        }
    }

    // 保存処理時コールバック
    // $data: 保存対象データ
    protected function _pending_cancel($data)
    {
        if( $this->session->admin->flg_approval ) show_404();

        // 保存処理
        $data['status'] = APPROVAL_STATUS_DRAFT;
        $data['pending_by'] = '';
        $data['pending_date'] = '';
        $save_id = $this->_model->save($data, $this->_model_id, true);
        if ($save_id) { // 保存成功

            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認取消', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );

            // フラッシュメッセージ設定
            $edit_btn = '<a href="'.admin_base_url($this->_controller_name.'/edit').'" type="button" class="btn btn-primary btn-xs">再度編集する</a>';
            $this->flash->info(
                sprintf('ID:%s「%s」の承認依頼を取り下げました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認依頼', false, "公開保存対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の承認依頼に失敗しました', $this->_model_id)
            );
        }
    }

    // 保存処理時コールバック
    // $data: 保存対象データ
    protected function _reject($data)
    {
        if( !$this->session->admin->flg_admin ) show_404();

        // 保存処理
        $data['status'] = APPROVAL_STATUS_REJECT;
        $save_id = $this->_model->save($data, $this->_model_id, true);
        if ($save_id) { // 保存成功
        	$to = $this->_model->get_approver_emails();
            if( !empty($to) && ENABLE_NOTIFY_APPROVAL ){
                //$this->load->library('email');
                //$this->email
                //    ->template('approval_reject', [
                //        'entity_name'   => $this::ENTITY_NAME,
                //        'post_status'   => '差戻し',
                //        'post_title'    => $data['title'],
                //        'post_edit_url' => current_url(),
                //        'user'          => $this->my_session->admin,
                //        'message'       => !empty($data['note'])? $data['note']:'(ありません)',
                //    ])
                //    ->to($to)
                //    ->send(false);
            }

            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '差戻し', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $edit_btn = '<a href="'.admin_base_url($this->_controller_name.'/edit').'" type="button" class="btn btn-primary btn-xs">再度編集する</a>';
            $this->flash->info(
                sprintf('ID:%s「%s」の差戻しをしました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '差戻し保存', false, "公開保存対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の差戻しに失敗しました', $this->_model_id)
            );
        }
    }

    // 保存処理時コールバック
    // $data: 保存対象データ
    protected function _approved($data)
    {
        if( !$this->session->admin->flg_admin ) show_404();
        
        $ori_model_id = 0;
        if( !empty($this->_current_date) && !empty($this->_current_date['parent_id']) ){
            // 記事の差し替え
            $ori_model_id = $this->_model_id;
            $this->_model_id = $this->_current_date['parent_id'];
        }

        // 保存処理
        $data['status'] = APPROVAL_STATUS_PUBLISHED;
        $save_id = $this->_model->save($data, $this->_model_id, true);
        if ($save_id) { // 保存成功
            
            // 元記事は削除
            if( !empty($ori_model_id) ) $this->_model->delete($ori_model_id);

        	$to = $this->_model->get_approver_emails();
            if( !empty($to) && ENABLE_NOTIFY_APPROVAL ){
                //$this->load->library('email');
                //$this->email
                //    ->template('approval_reject', [
                //        'entity_name'   => $this::ENTITY_NAME,
                //        'post_status'   => '差戻し',
                //        'post_title'    => $data['title'],
                //        'post_edit_url' => current_url(),
                //        'user'          => $this->my_session->admin,
                //        'message'       => !empty($data['note'])? $data['note']:'(ありません)',
                //    ])
                //    ->to($to)
                //    ->send(false);
            }

            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認OK', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $edit_btn = '<a href="'.admin_base_url($this->_controller_name.'/edit').'" type="button" class="btn btn-primary btn-xs">再度編集する</a>';
            $this->flash->info(
                sprintf('ID:%s「%s」の承認OK（公開）しました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認OK', false, "公開保存対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の承認OKに失敗しました', $this->_model_id)
            );
        }
    }

    // 下書き保存時コールバック(編集画面経由)
    protected function _pending_midflow($data)
    {
        // 下書き保存処理
        $data['status'] = APPROVAL_STATUS_PENDING;
        $save_id = $this->_model->save($data, $this->_model_id, false);
        if ($save_id) { // 保存成功
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認依頼中の途中保存', true, "保存時のタイトル:". strip_tags($this->_model->get_last_title())
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('ID:%s「%s」の承認依頼中の途中保存が完了しました', $save_id, strip_tags($this->_model->get_last_title()))
            );
        } else { // 失敗
            // 操作ログ保存
            $this->_save_ope_log(
                $save_id, '承認依頼中の途中保存', false, "下書き対象ID:". $this->_model_id
            );
            // フラッシュメッセージ設定
            $this->flash->error(
                sprintf('ID:%s の承認依頼中の途中保存に失敗しました', $this->_model_id)
            );
        }
    }
}
