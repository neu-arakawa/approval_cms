<?php

// 標準lang()の上書き
// 対応する翻訳データがなければそのまま使用する
function lang($str, $for = '', $attributes = array())
{
    $ci = get_instance();
    $line = $ci->lang->line($str, false);
    if (empty($line)) {
        $line = $str;
    }
    if ($for !== '')
    {
        $line = '<label for="'.$for.'"'._stringify_attributes($attributes).'>'.$line.'</label>';
    }

    return $line;
}
