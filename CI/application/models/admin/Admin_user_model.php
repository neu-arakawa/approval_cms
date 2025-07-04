<?php
class Admin_user_model extends MY_Model
{
    protected $_table = 'admin_users';
    protected $_title_field = 'name';          // 見出しとして使用するフィールド名
    protected $_search_options = [
        'order_by' => ['modified'=>'DESC', 'id'=>'DESC'],
        'limit' => DEFAULT_DB_LIMIT,
    ];
    protected $_search_fields = [ // 検索処理用の定義
        'keyword' => ['type'=>'query', 'method'=>'_search_keyword', 'field'=>[AUTH_USER_FIELD, 'name', AUTH_EMAIL_FIELD]],
    ];
    protected $_search_sorts = [ // 検索用ソートキー定義
        'id' => ['id'=>'ASC'],
        AUTH_USER_FIELD => [AUTH_USER_FIELD=>'ASC'],
        'name' => ['name'=>'ASC'],
        AUTH_EMAIL_FIELD => ['email'=>'ASC'],
    ];

    // 保存前処理
    // オーバーライドして使用
    // $data: 保存対象データ
    // $id: 保存対象のID(新規の場合はnull)
    // $raw: 指定された生のデータ
    // 戻り値： 加工した保存対象データ or エラー時はfalse
    protected function _before_save($data, $id=null, $raw=[], $context='')
    {
        if (!empty($id) && empty($raw['edit_password'])) {
            // 編集時、パスワードの変更を行わない場合は値を削除
            unset($data['password']);
        }

        if (!empty($data['password'])) {
            // パスワードをハッシュ化
            $data[AUTH_PASSWORD_FIELD] = password_hash($data[AUTH_PASSWORD_FIELD], PASSWORD_DEFAULT);
        }

        if (empty($this->my_session->admin->flg_admin)) { // 管理権限なし
            if (isset($data['flg_role'])) {
                unset($data['flg_role']);
            }
            if (isset($data['login_name'])) {
                unset($data['login_name']);
            }
            if (isset($data['flg_acl'])) {
                unset($data['flg_acl']);
            }
        } else { // 管理権限あり
            if (!isset($data['role'])) { // 権限が設定されていなければデフォルトを設定する
                $data['role_id'] = USER_ROLE_AUTHOR;
            }
            
            // 機能自体不要
            $data['flg_acl'] = null;
        }
        return $data;
    }

    protected function _append_data($data)
    {
        unset($data['password']);
        if (!empty($data['flg_acl'])) {
            $data['flg_acl'] = array_filter(explode(',', $data['flg_acl']));
        }
        return $data;
    }
}
