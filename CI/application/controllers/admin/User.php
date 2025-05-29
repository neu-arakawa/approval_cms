<?php
class User extends Admin_Controller
{
    const ENTITY_NAME = 'ユーザ';
    const VIEW_ICON = '<i class="fas fa-user"></i>';
    protected $_exec_methods = [                        // 実行を許可するメソッド名
        'index', 'add', 'edit', 'delete',
    ];

    // 自分自身のデータ編集
    public function my_edit()
    {
        $this->_transition($this->my_session->admin->id, 'admin/user/my_edit');
    }
    // 編集完了時
    protected function _complete($data)
    {
        parent::_complete($data);
        if (empty($this->my_session->admin->flg_admin)) { // 管理権限なし
            admin_redirect('/');   // トップにリダイレクト
        } else { // 管理権限あり
            admin_redirect($this->_controller_name.'/index');   // 一覧にリダイレクト
        }
    }

    // 一覧
    public function index()
    {
        if (empty($this->my_session->admin->flg_admin)) { // 管理権限なし
            $this->_forbidden();
        }
        parent::index();
    }

    // 編集
    public function edit($id=null)
    {
        if (empty($this->my_session->admin->flg_admin)) { // 管理権限なし
            $this->_forbidden();
        }
        parent::edit($id);
    }

    // 削除
    public function delete($id)
    {
        if (empty($this->my_session->admin->flg_admin)) { // 管理権限なし
            $this->_forbidden();
        }
        parent::delete($id);
    }

    // ロックされたアカウントの解除
    public function unlock($id)
    {
        if (empty($this->my_session->admin->flg_admin)) { // 管理権限なし
            $this->_forbidden();
        }
        $this->load->model('Admin_model');
        $user = $this->Admin_model->get_by_id($id);
        if (empty($user) || AUTH_RETRY_MAX<0) { // ユーザ情報取得失敗、またはログイン試行制限の設定がない
            show_404();
        }

        // アカウントロック処理実行
        if ($this->Admin_model->reset_retry($user[AUTH_USER_FIELD])) { // 更新成功
            // 操作ログ保存
            $this->_save_ope_log(
                $user['id'], 'アカウントロック解除', true, "対象ID:". $user['id']. ' ログイン名:'. $user[AUTH_USER_FIELD]
            );
            // フラッシュメッセージ設定
            $this->flash->info(
                sprintf('ID:%s のアカウントロック解除に成功しました', $id)
            );
        } else {
            $this->_save_ope_log(
                $user['id'], 'アカウントロック解除', false, "対象ID:". $user['id']. ' ログイン名:'. $user[AUTH_USER_FIELD]
            );
            $this->flash->error(
                sprintf('ID:%s のアカウントロック解除に失敗しました', $id)
            );
        }
        admin_redirect('user/index');
    }

    // パスワードの必須チェック
    public function _validate_password_required($data)
    {
        if (empty($data)) { // 未入力
            if (empty($this->_model_id) ||
                (!empty($this->_model_id) && $this->input->post('edit_password')) ) {
                // 新規作成か、パスワード編集チェックが付いている場合は必須
                $this->form_validation->set_message('_validate_password_required', '{field}：必ず設定してください');
                return false;
            }
        }
        return true;
    }
}
