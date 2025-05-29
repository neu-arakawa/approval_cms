<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Session_files_driver extends CI_Session_files_driver {

    public function open($save_path, $name)
    {
        parent::open($save_path, $name);
        $this->_file_path = $this->_config['save_path'].DIRECTORY_SEPARATOR
            .'sess_' // アップローダーとの連携のため、セッションファイル名をデフォルトにする
            .($this->_config['match_ip'] ? md5($_SERVER['REMOTE_ADDR']) : '');
        return $this->_success;
    }

    /**
	 * Garbage Collector
	 *
	 * Deletes expired sessions
	 *
	 * @param	int 	$maxlifetime	Maximum lifetime of sessions
	 * @return	bool
	 */
    public function gc($maxlifetime)
    {
        if ( ! is_dir($this->_config['save_path']) OR ($directory = opendir($this->_config['save_path'])) === FALSE)
        {
            log_message('debug', "Session: Garbage collector couldn't list files under directory '".$this->_config['save_path']."'.");
            return $this->_failure;
        }

        $ts = time() - $maxlifetime;

        $pattern = ($this->_config['match_ip'] === TRUE)
            ? '[0-9a-f]{32}'
            : '';

        $pattern = sprintf(
            '#\A(%s|sess_)'.$pattern.$this->_sid_regexp.'\z#',
            preg_quote($this->_config['cookie_name'])
        );

         while (($file = readdir($directory)) !== FALSE)
         {
            // If the filename doesn't match this pattern, it's either not a session file or is not ours
            if ( ! preg_match($pattern, $file)
            OR ! is_file($this->_config['save_path'].DIRECTORY_SEPARATOR.$file)
            OR ($mtime = filemtime($this->_config['save_path'].DIRECTORY_SEPARATOR.$file)) === FALSE
            OR $mtime > $ts)
            {
                continue;
            }

            unlink($this->_config['save_path'].DIRECTORY_SEPARATOR.$file);
        }

        closedir($directory);
        return $this->_success;
    }

}