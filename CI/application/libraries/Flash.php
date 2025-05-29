<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Flash {
    private $CI;

    public function __construct($config = array())
    {
        $this->CI =& get_instance();
        if(!isset($this->CI->my_session)){
            $this->CI->load->library('my_session');
        }
    }

    // エラーメッセージ
    public function error($msg)
    {
        $this->CI->my_session->set_flashdata('error', $msg);
    }
    // お知らせメッセージ
    public function info($msg)
    {
        $this->CI->my_session->set_flashdata('info', $msg);
    }
    // 成功メッセージ
    public function success($msg)
    {
        $this->CI->my_session->set_flashdata('success', $msg);
    }
    // 注意メッセージ
    public function warning($msg)
    {
        $this->CI->my_session->set_flashdata('warning', $msg);
    }

    // フラッシュメッセージ表示
    // $type: 描画する種類（nullならFlashされているものは無差別に表示）
    // $dismiss: 閉じるアラート有効
    public function render($type=null, $dismiss=true)
    {
        $types = ['error', 'warning', 'success', 'info'];
        foreach($types as $t){
            $msg = $this->CI->my_session->flashdata($t);
            $this->CI->my_session->unmark_flash($t);
            if(!$msg) continue;
            if($type && $t!==$type) continue;

            $this->CI->load->view("flash/{$t}", ['message'=>$msg, 'dismiss'=>$dismiss]);
        }
    }
}
