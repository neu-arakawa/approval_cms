<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package CodeIgniter
 * @author  EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT  MIT License
 * @link    https://codeigniter.com
 * @since   Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Email extends CI_Email {

    public function template($name, $params=[]){
        if (($tmpl = $this->_load_template($name, $params)) === false ) {
            return false;
        }

        // メールをヘッダと本文とに分ける
        $tmpl = str_replace( "\r", '', $tmpl );
        if (!preg_match( '/^(.*?)\n\n(.*)$/s', $tmpl, $m)) return false;

        $this->clear(true);

        $header = trim( $m[1] );
        $body = trim( $m[2] );

        $headers = array();
        $to = null;
        $subject = null;
        $return_path = null;

        $arr = explode("\n", $header);
        foreach ($arr as $val) {
            if( !preg_match('/^(.+?)\:(.+)$/', $val, $m ) ){
                continue;
            }
            $key = ucfirst( strtolower(trim($m[1])) );
            $val = trim( $m[2] );

            switch ($key) {
                case 'From':
                    if (preg_match('/^(.+?)\s*<(.+?)>$/', $val, $m)) {
                        // 宛先名 <email> の形式の場合
                        $this->from(trim($m[2]), trim($m[1]));
                    } else {
                        $this->from($val);
                    }
                    break;
                case 'To':
                    $this->to($val);
                    break;
                case 'Cc':
                    $this->cc($val);
                    break;
                case 'Bcc':
                    $this->bcc($val);
                    break;
                case 'Reply-to':
                    if (preg_match('/^(.+?)\s*<(.+?)>$/', $val, $m)) {
                        // 宛先名 <email> の形式の場合
                        $this->reply_to(trim($m[2]), trim($m[1]));
                    } else {
                        $this->reply_to($val);
                    }
                    break;
                case 'Subject':
                    $this->subject($val);
                    break;
                default:
                    $this->set_header($key, $val);
                    break;
            }
        }
        $this->message($body);

        return $this;
    }

    protected function _load_template($name, $params=[])
    {
        $tmpl = EMAIL_PATH. '/'. $name. '.php';
        if (!file_exists($tmpl)) return false;

        if (!empty($params)) {
            // シンボルテーブルに変数を展開
            extract($params);
        }

        ob_start();
        if( (include "$tmpl") === false ) return false;
        $res = ob_get_contents();
        ob_end_clean();

        return $res;
    }
}
