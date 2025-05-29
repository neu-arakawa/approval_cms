<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Lang extends CI_Lang {

    public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
    {
        if ($this->_exists($langfile, $idiom, $add_suffix)) { // 言語ファイルが存在すればロード
            parent::load($langfile, $idiom, $return, $add_suffix, $alt_path);
        }
    }

    // 言語ファイルが存在するかチェックする
    protected function _exists($langfile, $idiom = '', $add_suffix = TRUE, $alt_path = '')
    {
        $langfile = str_replace('.php', '', $langfile);

        if ($add_suffix === TRUE) {
            $langfile = preg_replace('/_lang$/', '', $langfile).'_lang';
        }

        $langfile .= '.php';
        if (empty($idiom) OR ! preg_match('/^[a-z_-]+$/i', $idiom)) {
            $config =& get_config();
            $idiom = empty($config['language']) ? 'english' : $config['language'];
        }

        // Load the base file, so any others found can override it
        $basepath = BASEPATH.'language/'.$idiom.'/'.$langfile;
        if (file_exists($basepath)) return true;

        // Do we have an alternative path to look in?
        if ($alt_path !== '') {
            $alt_path .= 'language/'.$idiom.'/'.$langfile;
            if (file_exists($alt_path)) return true;
        } else {
            foreach (get_instance()->load->get_package_paths(TRUE) as $package_path) {
                $package_path .= 'language/'.$idiom.'/'.$langfile;
                if ($basepath !== $package_path && file_exists($package_path)) return true;
            }
        }

        return false;
    }
}
