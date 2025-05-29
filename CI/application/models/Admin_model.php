<?php

class Admin_model extends MY_Model
{
    protected $_table = AUTH_TABLE;

    // 認証処理
    public function auth($login_name, $password)
    {
        // ユーザテーブルデータを使用して認証
        $this->db->from($this->_table);
        $this->db->where(AUTH_USER_FIELD, $login_name);
        $row = $this->db->get()->row();

        if (empty($row)) return false;
        if (!password_verify($password, $row->{AUTH_PASSWORD_FIELD})) return false;
        
        if (isset($row->flg_acl)) {
            $row->flg_acl = explode(',', $row->flg_acl);
        }

        // セッションにセット
        $this->my_session->sess_regenerate();
        $this->my_session->admin = $row;

        return true;
    }

    // リトライ回数をカウントアップする
    public function increment_retry($login_name)
    {
        $count = $this->get_retry_count($login_name);
        if ($count===false) return false;
        $save = [
            'retry_count' => ++$count,
            'retry_failed' => NOW,
        ];
        return $this->db->update($this->_table, $save, [AUTH_USER_FIELD=>$login_name])
                ? $count
                : false;
    }
    // リトライ回数をリセットする
    public function reset_retry($login_name)
    {
        $save = [
            'retry_count' => 0,
            'retry_failed' => null,
        ];
        return $this->db->update($this->_table, $save, [AUTH_USER_FIELD=>$login_name]);
    }

    // リトライ回数を取得する
    public function get_retry_count($login_name)
    {
        $this->db->from($this->_table);
        $this->db->where(AUTH_USER_FIELD, $login_name);
        $row = $this->db->get()->row();
        if ($row===null) return false;
        return $row->retry_count ? $row->retry_count : 0;
    }
    // リトライの最終失敗日時を取得
    public function get_retry_failed($login_name)
    {
        $this->db->from($this->_table);
        $this->db->where(AUTH_USER_FIELD, $login_name);
        $row = $this->db->get()->row();
        if ($row===null) return false;
        return $row->retry_failed ? $row->retry_failed : null;
    }

    // パスワードのリセット処理
    public function reset($token, $password)
    {
        $save = [
            'access_token' => null,
            'token_expires' => null,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];
        return $this->db->update($this->_table, $save, ['access_token'=>$token])
                    ? true
                    : false;
    }

    // アクセストークンの発行と保存
    public function save_access_token($email)
    {
        $row = $this->get_by_email($email);
        if (empty($row)) return false;

        $this->db->reset_query();

        $save = [
            'access_token' => $this->_generate_access_token(),
            'token_expires' => date('Y-m-d H:i:s', time()+PASSWORD_RESET_EXPIRES*60),
        ];
        return $this->db->update($this->_table, $save, ['id'=>$row->id])
                    ? $save['access_token']
                    : false;
    }

    // アクセストークンの存在チェック
    public function check_access_token($token)
    {
        $row = $this->get_by_access_token($token);
        return empty($row) ? false : true;
    }
    // アクセストークンからユーザ情報を取得する
    public function get_by_access_token($token) {
        $this->db->from($this->_table);
        $this->db->where('access_token', $token);
        $row = $this->db->get()->row();
        return empty($row) ? false : $row;
    }
    // E-mailからユーザ情報を取得する
    public function get_by_email($email) {
        $this->db->from($this->_table);
        $this->db->where(AUTH_EMAIL_FIELD, $email);
        $row = $this->db->get()->row();
        return empty($row) ? false : $row;
    }

    // アクセストークンの発行
    private function _generate_access_token($len=30)
    {
        $possible = '0123456789abcdefghijklmnopqrstuvwxyz';
        $token = "";
        $i = 0;

        while ($i < $len) {
            $char = substr($possible, mt_rand( 0, strlen( $possible ) - 1 ), 1);
            if (!stristr( $token, $char)) {
                $token .= $char;
                $i++;
            }
        }
        return $token;
    }
}
