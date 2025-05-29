<?php

class MY_Form_validation extends CI_Form_validation {
    private $_group = null;

    // 下書きのバリデートの時に除去するルール名(正規表現)
    private $_ignore_if_draft = [
        '^required$', '^isset$', '^callback__required_any.*',
    ];

    public function __construct($rules = [])
    {
        parent::__construct($rules);

        // 現在のURLからURL単位でのグループ名を取得
        $group = $this->CI->router->class. '/'. $this->CI->router->method;
        if (method_exists($this->CI, 'get_admin_mode') && $this->CI->get_admin_mode()===true) {
            $group = 'admin/'. $group;
        }

        $this->init_group($group);
    }


    public function init_group($group)
    {
        $this->_group = $group;

        // labelを設定
        if (!empty($this->_config_rules[$this->_group]) && is_array($this->_config_rules[$this->_group])) {
            foreach ($this->_config_rules[$this->_group] as $k => $v) {
                if (empty($v['label'])) {
                    $this->_config_rules[$this->_group][$k]['label'] = 'lang:'.$v['field'];
                }
            }
        }
    }


    // 必須フィールドリストを返す
    public function is_required_field($field)
    {
        if (empty($this->_required_fields) && !empty($this->_config_rules[$this->_group]) && is_array($this->_config_rules[$this->_group])) {
            $rules = $this->_config_rules[$this->_group];
            foreach ($rules as $k => $v) {
                if (empty($v['field']) || $v['field']!=$field) continue;

                if (!empty($v['rules'])) {
                    if (is_array($v['rules'])) {
                        if (in_array('required', $v['rules'])) return true;
                    } else {
                        if (strpos('|'.$v['rules'].'|', '|required|') !== false) return true;
                    }
                }
            }
        }
        return false;
    }

    // 日付バリデーション
    public function valid_date($str)
    {
        if (empty($str)) {
            return false;
        }
        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $str, $m)) {
            return false;
        }
        $tmp = explode('-', $str);
        if (count($tmp) != 3) {
            return false;
        }
        $tmp = array_map('intval', $tmp);
        return checkdate($tmp[1], $tmp[2], $tmp[0]);
    }

    // 時刻バリデーション
    public function valid_time($str)
    {
        if (empty($str)) {
            return false;
        }
        if (!preg_match('/^(\d{1,2}):(\d{1,2})$/', $str, $m)) {
            return false;
        }
        $tmp = explode(':', $str);
        if (count($tmp) != 2) {
            return false;
        }
        $hour = (int)$tmp[0];
        $min = (int)$tmp[1];
        return $hour <= 23 && $hour >= 0 && $min <= 59 && $min >= 0;
    }

    // 日時バリデーション(時分まで）
    public function valid_datetime_minute($str)
    {
        return $this->_valid_datetime($str, 'minute');
    }

    // 日時バリデーション(時分秒まで）
    public function valid_datetime_second($str)
    {
        return $this->_valid_datetime($str, 'second');
    }

    // 日時バリデーション
    public function _valid_datetime($str, $mode)
    {
        if (empty($str)) {
            return false;
        }

        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})\s+(\d{1,2}):(\d{1,2})(:(\d{1,2}))?$/', $str, $m)) {
            return false;
        }

        if ($mode=='minute' && !empty($m[6]) ||
            $mode=='second' && empty($m[6])) return false;

        $m = array_map('intval', $m);
        return checkdate($m[2], $m[3], $m[1]) &&
                ($m[4] >= 0 && $m[4] <= 23) && ($m[5] >= 0 && $m[5] <= 59) && (($mode=='second' && $m[7] >= 0 && $m[7] <= 59) || $mode=='minute');
    }

    // 日時の順序バリデーション
    public function valid_datetime_order($str, $target)
    {
        $ed = $this->CI->input->post($target);
        if (empty($ed)) return true;

        $st = strtotime($str);
        $ed = strtotime($ed);
        return $st <= $ed;
    }
    public function valid_date_order($str, $target)
    {
        return $this->valid_datetime_order($str, $target);
    }

    // 年月バリデーション
    public function valid_yearmonth($str)
    {
        if (empty($str)) {
            return false;
        }
        if (!preg_match('/^(\d{4})-(\d{2})$/', $str, $m)) {
            return false;
        }
        return $m[2]>=1 && $m[2]<=12;
    }

    // アバウトな日時判定
    public function valid_datetime($str)
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?/', $str)) {
            return false;
        }

        $time = strtotime($str);
        return checkdate(date('n', $time), date('j', $time), date('Y', $time));
    }

    // 未来日付判定
    public function valid_future_datetime($str)
    {
        return strtotime($str) > time();
    }

    // パスワード強度のバリデーション
    public function valid_password($str)
    {
        return preg_match('/[a-zA-Z]+/', $str) &&
                preg_match('/[0-9]+/', $str) &&
                strlen($str) >= 8;
    }

    // ディレクトリのバリデーション
    public function valid_dirname($str)
    {
        return preg_match('/^[a-zA-Z0-9_\-]+$/', $str) ? true : false;
    }

    public function valid_url_path($str) {
        // パターンにマッチするかどうかチェック
        if (!preg_match('/^[a-zA-Z0-9_\-\/]+$/', $str)) {
            return FALSE;
        }
        // 末尾が/でないかどうかチェック
        if (substr($str, -1) === '/') {
            return FALSE;
        }
        return TRUE;
    }

    // ユニークチェック
    // 除外するidを考慮する
    public function is_unique_by_id($str,$field) {
        $model_id = $this->CI->get_model_id(); // 編集対象のIDを取得
        sscanf($field, '%[^.].%[^.]', $table, $field);
        if (isset($this->CI->db)) {
            $db = $this->CI->db
                    ->from($table)
                    ->where($field, $str)
                    ->limit(1);
            if (!empty($model_id)) {
                $db->where('id <>', $model_id);
            }
            if ($db->count_all_results()===0) return true;
        }
        return false;
    }

    // フィールドに対応するバリデーションルールを返す
    public function getConfigRule($field)
    {
        $group = preg_replace('#/\d*$#', '', $this->CI->uri->ruri_string());

        if (empty($this->_config_rules[$group])) {
            $group = $this->CI->router->class."/".$this->CI->router->method;
            if (empty($this->_config_rules[$group])) {
                return false;
            }
        }

        // 数字添字の場合
        if (empty($this->_config_rules[$group][$field])) {
            foreach ($this->_config_rules[$group] as $k => $v) {
                if ($v['field'] == $field) {
                    return $v;
                }
            }
            return false;
        }

        return $this->_config_rules[$group][$field];
    }

    // オーバーライド
    // $draft: 下書き用のバリデーション（必須チェック系のルールは除外する）
    public function run($config = NULL, &$data = NULL, $draft = false)
    {
        $validation_array = empty($this->validation_data)
            ? $_POST
            : $this->validation_data;

        // Does the _field_data array containing the validation rules exist?
        // If not, we look to see if they were assigned via a config file
        if (count($this->_field_data) === 0)
        {
            // No validation rules?      We're done...
            if (empty($this->_config_rules))
            {
                return FALSE;
            }

            if (empty($config))
            {
                // Is there a validation rule for the particular URI being accessed?
                $config = trim($this->CI->uri->ruri_string(), '/');
                isset($this->_config_rules[$config]) OR $config = $this->CI->router->class.'/'.$this->CI->router->method;
            }

            $this->set_rules(isset($this->_config_rules[$config]) ? $this->_config_rules[$config] : $this->_config_rules);

            // Were we able to set the rules correctly?
            if (count($this->_field_data) === 0)
            {
                log_message('debug', 'Unable to find validation rules');
                return FALSE;
            }
        }

        // Load the language file containing error messages
        $this->CI->lang->load('form_validation');

        // Cycle through the rules for each field and match the corresponding $validation_data item
        foreach ($this->_field_data as $field => &$row)
        {
            // Fetch the data from the validation_data array item and cache it in the _field_data array.
            // Depending on whether the field name is an array or a string will determine where we get it from.
            if ($row['is_array'] === TRUE)
            {
                $this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
            }
            elseif (isset($validation_array[$field]))
            {
                $this->_field_data[$field]['postdata'] = $validation_array[$field];
            }
        }

        // Execute validation rules
        // Note: A second foreach (for now) is required in order to avoid false-positives
        //   for rules like 'matches', which correlate to other validation fields.
        foreach ($this->_field_data as $field => &$row)
        {
            // Don't try to validate if we have no rules set
            if (empty($row['rules']))
            {
                continue;
            }

            if ($draft) {
                foreach ($row['rules'] as $idx => $rule) {
                    foreach ($this->_ignore_if_draft as $reg) {
                        if (preg_match("/{$reg}/", $rule)) {
                            unset($row['rules'][$idx]);
                            break;
                        }
                    }
                }
            }

            $this->_execute($row, $row['rules'], $row['postdata']);
        }

        if ( ! empty($this->_error_array))
        {
            return FALSE;
        }

        // Fill $data if requested, otherwise modify $_POST, as long as
        // set_data() wasn't used (yea, I know it sounds confusing)
        if (func_num_args() >= 2)
        {
            $data = empty($this->validation_data) ? $_POST : $this->validation_data;
            $this->_reset_data_array($data);
            return TRUE;
        }

        empty($this->validation_data) && $this->_reset_data_array($_POST);
        return TRUE;
    }

    // バリデーションエラー設定
    // 標準のバリデーション処理では、
    // POST値のグループでエラー処理をする機能がないため、
    // カスタムでエラー処理を行う場合に呼び出す用のメソッド
    public function set_validate_error($field, $error) {
        $this->_field_data[$field]['error'] = $error;
        $this->_field_data[$field]['postdata'] = null;
        $this->_error_array[$field] = $error;
    }

     // 指定したフィールドの required を設定する
    public function set_required($group, $field, $label=null) {
        $this->set_rule($group, $field, 'required', $label);
    }

    // 指定したフィールドの ruleを追加する
    public function set_rule($group, $field, $rule, $label=null, $first=true)
    {
        // 数字添字の場合
        if (empty($this->_config_rules[$group][$field])) {
            foreach ($this->_config_rules[$group] as $k => $v) {
                if ($v['field'] == $field) {
                    if(!isset($v['rules'])){
                        $this->_config_rules[$group][$k]['rules'] = $rule;
                    }else{
                        $rules = explode("|",$v['rules']);
                        if($first){
                            array_unshift($rules, $rule);
                        }else{
                            $rules[] = $rule;
                        }
                        $rules = array_filter(array_unique($rules));
                        $this->_config_rules[$group][$k]['rules'] = implode('|',$rules);
                    }
                    return;
                }
            }
            // fieldが見つからない場合は追加
            $this->_config_rules[$group][] = [
                'field' => $field,
                'rules' => $rule,
                'label' => $label ? $label : 'lang:'.$field,
            ];
        } else {
            if(!isset($this->_config_rules[$group][$field]['rules'])){
                $this->_config_rules[$group][$field]['rules'] = $rule;
            }else{
                $rules = explode("|",$this->_config_rules[$group][$field]['rules']);
                if($first){
                    array_unshift($rules, $rule);
                }else{
                    $rules[] = $rule;
                }
                $rules = array_unique($rules);
                $this->_config_rules[$group][$field]['rules'] = implode('|',$rules);
            }
        }
        return;
    }

    // バリデーションルールを削除
    public function unset_rule($group, $field)
    {
        // 数字添字の場合
        if (empty($this->_config_rules[$group][$field])) {
            foreach ($this->_config_rules[$group] as $k => $v) {
                if ($v['field'] == $field) {
                    unset($this->_config_rules[$group][$k]);
                    return true;
                }
            }
            return false;
        }
        unset($this->_config_rules[$group][$field]);
        return true;
    }
}
