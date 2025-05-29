<?php
require_once(BASEPATH.'libraries/Session/Session.php');
class MY_Session extends CI_Session {

    public function __construct(array $params = array())
    {
        // セッションが有効でない場合のみ、セッションを開始するために親コンストラクタを呼び出す
        if (session_status() !== PHP_SESSION_ACTIVE) parent::__construct($params);
    }

    public function __set($key, $value)
    {
        // ピリオド区切りのキー名を分割する
        $arr = explode('.', $key);
        $arr = array_reverse($arr);
        $last_key = array_shift($arr);
        $result = [$last_key => $value];
        foreach ($arr as $k){
            $tmp = $result;
            $result = [
                $k => $tmp,
            ];
        }
        $key = key($result);
        $value = $result[$key];

        // var_dump(['key'=>$key,'value'=>$value, 'result'=>$result]);
        // ピリオド区切りのキー名が指定された場合は
        // 多階層のセッション値として保存する
        if (is_array($value)) {
            $this->_set_recursive($result, $_SESSION);
        } else {
            parent::__set($key, $value);
        }
    }

    public function __get($key)
    {
        // ピリオド区切りのキー名を分割する
        $keys = explode('.', $key);
        return $this->_get_recursive($keys, $_SESSION);
    }

    public function __unset($key)
    {
        $keys = explode('.', $key);
        $this->_unset_recursive($keys, $_SESSION);
    }

    // 多階層のセッション値の保存のための、再帰関数
    private function _set_recursive($arr, &$sess)
    {
        if (!is_array($sess)) $sess = [];
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $this->_set_recursive($val, $sess[$key]);
            } else {
                $sess[$key] = $val;
            }
        }
    }

    // 多階層のセッション値の取得のための、再帰関数
    private function _get_recursive($keys, &$sess)
    {
        $key = array_shift($keys);

        if (count($keys)>0) {
            return $this->_get_recursive($keys, $sess[$key]);
        } else {
            return isset($sess[$key]) ? $sess[$key] : null;
        }
    }

    private function _unset_recursive($keys, &$sess)
    {
        $key = array_shift($keys);

        if (!isset($sess[$key])) return;
        if (count($keys)>0) {
            $this->_unset_recursive($keys, $sess[$key]);
        } else {
            unset($sess[$key]);
        }
    }
}
