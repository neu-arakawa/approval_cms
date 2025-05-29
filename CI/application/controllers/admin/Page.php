<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends Admin_Controller {

    protected $_allowed_methods = ['preview'];
    protected $_exec_methods = [                        // 基本操作系メソッドの中で実行を許可するメソッド(この設定はAdminControllerに定義されていないメソッドには影響を与えない)
        'index',
    ];

    public function index()
    {
        $view_vars = [];
        $view_vars['title'] = 'トップ';

        // 最近の記事取得
        // $recent_model = ['Admin_column_model', 'Admin_news_model'];
        // foreach ($recent_model as $name) {
        //     $this->load->model('admin/'.$name);
        //     $view_vars[$name] = $this->{$name}->search(['limit'=>5, 'order_by'=>['modified'=>'DESC']]);
        // }

        // 最近の操作履歴取得
        $where = [];
        $limit = 10;
        if (!$this->my_session->admin->flg_admin) {
            // 管理者でない場合は、
            // 自分のログを取得する
            $where = [
                'login_name' => $this->my_session->admin->login_name,
            ];
            $limit = 20;
        }
        $this->load->model('admin/Admin_log_model');
        $view_vars['logs'] = $this->Admin_log_model->search(['where'=>$where, 'limit'=>$limit, 'order_by'=>['created'=>'DESC']]);

        $this->load->view('admin/top', $view_vars);
    }

    // プレビューアクション
    public function preview()
    {
        unset($this->my_session->allow_preview);

        if (empty($this->input->get_post('url'))) { // プレビューURLが未指定
            show_404();
        }

        if (!$this->_preview_auth()) show_404(); // プレビュー認証

        $this->my_session->allow_preview = 1;

        $url = $this->input->get_post('url').'?preview=1';
        $data = [
            'url' => $this->input->get_post('url').'?preview=1',
            'hf_prefix' => 'blank',                                 // 空のヘッダ／フッタを指定
        ];
        $this->load->view('admin/preview', $data);
    }

    public function styles()
    {
        $this->load->view('admin/styles');
    }
}
