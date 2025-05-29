<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Admin_Controller {

    const ENTITY_NAME = '認証';
    protected $_allowed_methods = ['login', 'request_reset', 'reset'];                 // 未ログインでもアクセスを許可するメソッド名

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->_model = $this->Admin_model;
    }

    // ログイン
    public function login()
    {
        if ($this->my_session->admin) {
            // すでにログイン済みの場合
            admin_redirect('/');
        }

        if ($this->input->method()=='post') { // POST送信
            $login_name = $this->input->post('login_name');
            $password = $this->input->post('password');

            if (!$login_name || !$password) {
                // 認証情報未入力
                $this->flash->error('ログインに失敗しました');
            } else {

                // アカウントロック状態の確認
                if (AUTH_RETRY_MAX<0 || ($retry=$this->_model->get_retry_count($login_name))===false ) {
                    // リトライ設定がないか、対象のアカウントが存在しない
                    $locked = false;
                } else { // 対象のアカウントが存在する
                    if ($retry>=AUTH_RETRY_MAX) {
                        // ログイン試行回数が上限に達している

                        // var_dump(date('Y-m-d H:i:s',@strtotime($this->_model->get_retry_failed($login_name))));
                        // var_dump(date('Y-m-d H:i:s',@strtotime($this->_model->get_retry_failed($login_name) + AUTH_LOCK_RECOVER*60)));
                        if (AUTH_LOCK_RECOVER>=0 &&
                            NOW_TIME > @strtotime($this->_model->get_retry_failed($login_name)) + AUTH_LOCK_RECOVER*60) {
                            // ロック後、解除時間が経過
                            $locked = false;
                        } else {
                            // アカウントロック中
                            $locked = true;
                        }
                    } else {
                        $locked = false;
                    }
                }

                if ($locked) { // アカウントロック中
                    $msg = "連続して認証に失敗したため、アカウントがロックされています。\n";
                    if (AUTH_LOCK_RECOVER>=0) { // 復帰時間設定有り
                        $msg .= "しばらく経ってから再度ログインを行うか、管理者に連絡してロック解除を行ってから再度ログインを行ってください。";
                    } else {
                        $msg .= "管理者に連絡し、ロック解除を行ってから再度ログインを行ってください。";
                    }
                    $this->flash->error($msg);

                    // 操作ログの保存
                    $this->_save_ope_log(
                        null, 'ログイン', false, 'アカウントロック中の認証試行', $login_name
                    );
                } else if (!$this->_model->auth($login_name, $password)) {
                    // ログイン失敗(認証エラー)

                    $retry = $this->_model->increment_retry($login_name); // 認証試行回数をカウントアップ
                    if ($retry === false || AUTH_RETRY_MAX < 0) {
                        // 対象のアカウントが存在しない、またはリトライの設定がない場合
                        $flash_msg = "ログインに失敗しました";
                        $log_msg = '認証失敗';
                    } else if ($retry >= AUTH_RETRY_MAX) {
                        // 認証試行回数の上限に達した
                        $flash_msg = "ログインに失敗しました\n連続して認証に失敗したため、アカウントがロックされました。";
                        $log_msg = '認証失敗　アカウントのロック実施';
                    } else {
                        // 認証試行回数の上限に達していない
                        $flash_msg = "ログインに失敗しました";
                        $flash_msg .= "\nあと".(AUTH_RETRY_MAX-$retry)."回の失敗でアカウントがロックされます。";
                        $log_msg = '認証失敗';
                    }
                    $this->flash->error($flash_msg);

                    // 操作ログの保存
                    $this->_save_ope_log(
                        null, 'ログイン', false, $log_msg, $login_name
                    );

                } else { // ログイン成功
                    $this->_model->reset_retry($login_name); // 認証試行回数をリセット
                    $this->_save_ope_log(
                        null, 'ログイン', true, null
                    );
                    if (!empty($this->my_session->admin_request_url)) {
                        // ログイン以前にアクセスしていたURLに転送
                        $url = $this->my_session->admin_request_url;
                        unset($this->my_session->admin_request_url);
                        redirect($url);
                    }
                    admin_redirect('/');
                }
            }
        }

        $this->load->view('admin/auth/login',['is_login'=>true, 'title'=>'ログイン']);
    }

    // ログアウト
    public function logout()
    {
        $this->_save_ope_log(
            null, 'ログアウト', true, null
        );
        unset($_SESSION['admin']);
        admin_redirect('/login');
    }

    // パスワードリマインダ
    public function request_reset()
    {
        if (!PASSWORD_RESET_ENABLED) show_404(); // パスワードリセット無効

        if ($this->input->method()=='post') { // POST送信
            // チェック
            $email = $this->input->post(AUTH_EMAIL_FIELD);
            if (!empty($email)) {
                if (($token = $this->_model->save_access_token($email)) !== false) {
                    // リクエスト成功
                    $reset_url = admin_base_url('reset/'.h($token), null, true);
                    $this->load->library('email');
                    $this->email
                        ->template('request_reset', ['reset_url'=>$reset_url, 'email'=>$email])
                        ->to($email)
                        ->send(false);
                    // var_dump($this->email->print_debugger());
                    // exit();

                    $user = $this->_model->get_by_email($email);
                    $this->_save_ope_log(
                        null, 'パスワードリセット要求', true, 'E-mail:'.$email, $user->{AUTH_USER_FIELD}
                    );

                    $this->load->view('admin/auth/request_reset_done',['is_login'=>true]);
                    return;
                } else {
                    // リクエスト失敗
                    $this->_save_ope_log(
                        null, 'パスワードリセット要求', false, 'E-mail不一致によるエラー E-mail:'.$email
                    );
                }
            }

            $this->flash->error("入力されたメールアドレスが正しくありません\nご確認の上、再度ご入力ください");
        } else {

        }
        $this->load->view('admin/auth/request_reset',['is_login'=>true]);
    }

    // アクセストークンのチェック
    public function reset($token)
    {
        if (!PASSWORD_RESET_ENABLED) show_404(); // パスワードリセット無効

        if (empty($token) || !$this->_model->check_access_token($token)) {
            $this->flash->error("リンクが正しくないため、処理を続行できません。\nメールに記載されているURLで正しくアクセスできているか、またはリンクの送信から". PASSWORD_RESET_EXPIRES. '分以上経過していないかご確認ください');

            $this->_save_ope_log(
                null, 'パスワードリセット', false, 'アクセストークン不一致によるエラー token:'.$token
            );

            $this->load->view('admin/auth/error',['is_login'=>true]);
            return;
        }

        if ($this->input->method()=='post') {
            // POST送信された
            // パスワードのバリデーションチェック
            $validated = $this->form_validation->run('admin/auth/reset');
            if ($validated) { // バリデーション成功
                $user = $this->_model->get_by_access_token($token); // トークンからユーザ情報を取得する

                $ret = $this->_model->reset($token, $this->input->post('password'));
                if ($ret) { // 処理成功
                    $this->my_session->admin = null;
                    $this->flash->success("パスワードのリセットが完了しました。再度ログインを行ってください");
                    $this->_save_ope_log(
                        null, 'パスワードリセット', true, 'E-mail:'.$user->{AUTH_EMAIL_FIELD}, $user->{AUTH_USER_FIELD}
                    );

                    // ログインページにリダイレクト
                    admin_redirect('/login');
                } else { // 処理失敗
                    $this->flash->error("パスワードリセット時にエラーが発生しました");
                    $this->_save_ope_log(
                        null, 'パスワードリセット', false, 'リセット処理エラー E-mail:'.$user->{AUTH_EMAIL_FIELD}, $user->{AUTH_USER_FIELD}
                    );
                    $this->load->view('admin/auth/error',['is_login'=>true]);
                }
                return;
            }
        }

        $this->load->view('admin/auth/reset',['is_login'=>true]);
    }

}
