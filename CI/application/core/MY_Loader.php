<?php

// PHP8.2対応
#[AllowDynamicProperties]
class MY_Loader extends CI_Loader
{
    private $_my_vars = [];

    public function view($view, $vars = [], $return = false)
    {
        // セキュリティ対策
        // header('X-Frame-Options: SAMEORIGIN');
        // header('X-XSS-Protection: 1; mode=block');
        // header('X-Content-Type-Options: nosniff');

        // 共通部分を読み込む処理
        $view_paths = explode('/', $view);
        switch ($view_paths[0]) {
        case 'admin':
            $CI = get_instance();
            // 確認画面判定用
            $confirm_flag = false;
            if (end($view_paths) == 'confirm') {
                $CI->set_confirm_flag(true);
                $confirm_flag = true;
            }
            // viewにコントローラ・メソッドを渡す
            $qs = !empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
            $data = [
                'controller'   => $CI->router->class,
                'method'       => $CI->router->method,
                'view_path'    => VIEWPATH.$view.'.php',
                'confirm_flag' => $confirm_flag,
                'search_limits' => $CI->get_search_limits(),
                'entity_name' => $CI::ENTITY_NAME,
                'this_url' => uri_string(). $qs,
            ];
            if (is_object($vars)) {
                $vars = (array) $vars;
            }
            $vars = array_merge($vars, $data);

            $header = 'admin/common/header';
            $footer = 'admin/common/footer';

            // ヘッダー・フッターを変える
            if (!empty($vars['hf_prefix'])) {
                $header = 'admin/common/'.$vars['hf_prefix'].'_header';
                $footer = 'admin/common/'.$vars['hf_prefix'].'_footer';
                unset($vars['hf_prefix']);
            }

            if ($return) {
                $content  = parent::view($header, $vars, $return);
                $content .= parent::view($view,   $vars, $return);
                $content .= parent::view($footer, $vars, $return);
                return $content;
            } else {
                parent::view($header, $vars);
                parent::view($view,   $vars);
                parent::view($footer, $vars);
            }
            break;
        default:
            if ($return) {
                $content  = '';
                $content .= parent::view($view, $vars, $return);
                return $content;
            } else {
                $CI = get_instance();
                if(method_exists($CI,'_post_filter')){
                    $this->vars($vars);
                    $CI->_post_filter();
                    parent::view($view);
                }else{
                    parent::view($view, $vars);
                }
            }
        }
        $this->_my_vars = $vars;
    }

    // コントローラのロード
    public function load_controller($controller)
    {
        if (empty($controller)) {
            return false;
        }

        $path = '';
        // Is the controller in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($controller, '/')) !== FALSE) {
            // The path is in front of the last slash
            $path = substr($controller, 0, $last_slash + 1);

            // And the controller name behind it
            $controller = substr($controller, $last_slash + 1);
        }

        $CI =& get_instance();

        $controller = ucfirst(strtolower($controller));

        $mod_path = APPPATH;
        if (!file_exists($mod_path.'controllers/'.$path.$controller.'.php')) {
            return false;
        }

        if (!class_exists('CI_Controller')) {
            load_class('Controller', 'core');
        }

        require_once($mod_path.'controllers/'.$path.$controller.'.php');
        return $controller;
    }

    public function get_my_vars()
    {
        return $this->_my_vars;
    }
}
