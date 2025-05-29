<?php

function my_form_input ($data = '', $value = '', $extra = [])
{
    // POSTされた値を取得 (htmlエスケープしない)
    $value = set_value(_get_field($data), $value, false);
    if (!is_string($value)) $value = '';
    return form_input($data, $value, $extra);
}


function my_form_password($data = '', $value = '', $extra = [])
{
    // POSTされた値を取得 (htmlエスケープしない)
    $value = set_value(_get_field($data), $value, false);
    if (!is_string($value)) $value = '';
    return form_password($data, $value, $extra);
}


function my_form_textarea($data = '', $value = '', $extra = [])
{
    // POSTされた値を取得 (htmlエスケープしない)
    $value = set_value(_get_field($data), $value, false);
    if (!is_string($value)) $value = '';
    return form_textarea($data, $value, $extra);
}


function my_form_dropdown($field = '', $options = array(), $selected = array(), $extra = [])
{
    // GET / POST送信されている場合は、$value値としてそちらを使用する
    $CI =& get_instance();
    $req_val = $CI->input->get($field, FALSE) ? $CI->input->get($field, FALSE) : $CI->input->post($field, FALSE);
    if ($req_val !== null) {
        $selected = $req_val;
    }

    return form_dropdown($field, $options, $selected, $extra);
}


function my_form_checkbox($data = '', $value = '', $checked = false, $extra = [])
{
    $field = _get_field($data);

    // POSTされた値を取得 (htmlエスケープしない)
    $post_value = set_value($field, null, false);

    if (!is_null($post_value)) {
        $checked = false;
        if (!is_null($value) && $post_value == $value) {
            $checked = true;
        } else if (isset($data['value']) && !is_null($data['value']) && $post_value == $data['value']) {
            $checked = true;
        }
    }

    $tag  = '';
    $tag .= form_hidden($field, 0);
    $tag .= form_checkbox($data, $value, $checked, $extra);
    return $tag;
}


function my_form_checkboxes($field, $options = [], $value = null, $extra = [])
{
    // GET / POST送信されている場合は、$value値としてそちらを使用する
    $CI =& get_instance();
    $req_val = $CI->input->get($field, FALSE) ? $CI->input->get($field, FALSE) : $CI->input->post($field, FALSE);
    if ($req_val !== null) {
        $value = $req_val;
    }

    // デフォルト値
    $tag = '';
    $is_array = is_array(reset($options)) ? true : false; // optionsが多次元配列かどうか

    $tag .= form_hidden($field, '');
    foreach ($options as $v => $labels) {
        if ($is_array) {
            $tag .= '<div class="checkbox-group">';
            $tag .= '<label class="title">'.$v.'</label>';
        } else {
            $a = [];
            $a[$v] = $labels;
            $labels = $a;
        }

        foreach ($labels as $v => $label) {
            $checked = false;

            if (!empty($value)) {
                if (is_array($value)) {
                    $checked = in_array($v, $value);
                } else {
                    $checked = (string)$v === (string)$value;
                }
            }
            $tag .= '<label class="checkbox">'.form_checkbox($field, $v, $checked, $extra).$label.'</label>';
        }
        if ($is_array) {
            $tag .= '</div>';
        }
    }

    return $tag;
}


function my_form_radio($data = '', $value = '', $checked = false, $extra = [])
{
    // POSTされた値を取得 (htmlエスケープしない)
    $post_value = set_value(_get_field($data), null, false);
    if (!is_null($post_value)) {
        $checked = false;
        if (!is_null($value) && $post_value == $value) {
            $checked = true;
        } else if (isset($data['value']) && !is_null($data['value']) && $post_value == $data['value']) {
            $checked = true;
        }
    }

    return form_radio($data, $value, $checked, $extra);
}


function my_form_radios($field, $options = [], $value = null, $extra = [])
{
    // デフォルト値
    $value = set_value($field, $value, false);
    $tmp = [];
    foreach ($options as $v => $label) {
        $checked = !is_null($value) && (string)$v === (string)$value ? true : false ;
        $tmp[] = '<label class="radio">'.form_radio($field, $v, $checked, $extra).$label.'</label>';
    }

    return implode('', $tmp);
}


function my_form_hidden($name, $value = '', $recursing = false)
{
    // POSTされた値を取得 (htmlエスケープしない)
    $value = set_value($name, $value, false);
    return form_hidden($name, $value);
}


function my_form_submit($name = '', $content = '', $extra = [])
{
    return form_button(array_merge(['name'=>$name,'value'=>$name,'content'=>$content,'type'=>'submit'],$extra));
}


// フィールド名取得
function _get_field($data)
{
    $field = '';

    if (is_array($data)) {
        if (array_key_exists('name', $data)) {
            $field = $data['name'];
        }
    } else {
        $field = $data;
    }

    return $field;
}


// $_GETに対応させる get_postは配列field非対応のためget(),post()を使用
function set_value($field, $default = '', $html_escape = TRUE)
{
    $CI =& get_instance();
    $value = (isset($CI->form_validation) && is_object($CI->form_validation) && $CI->form_validation->has_rule($field))
           ? $CI->form_validation->set_value($field, $default)
           : (!is_null($CI->input->get($field, FALSE)) ? $CI->input->get($field, FALSE): $CI->input->post($field, FALSE));
    isset($value) OR $value = $default;
    return ($html_escape) ? html_escape($value) : $value;
}


// ソートリンク
// $sort_key: ソートのキー名(実際のソート条件はmodelの$_search_sortsに記述)
// $label: 表示ラベル名
// $attributes: aタグに付加する属性名
function sort_link($sort_key, $label, $attributes=[])
{
    $label = h(lang($label)); // ラベル定義参照

    $CI = get_instance();
    $curr_key = h($CI->input->get_post('sort'));
    $curr_dir = strtoupper(h($CI->input->get_post('direction')));

    if ($curr_key == $sort_key) { // 現在の表示が指定のソートと一致する場合
        if (empty($curr_dir) || !in_array($curr_dir, ['ASC','DESC'])) $dir = 'ASC';
        else if ($curr_dir == 'ASC') $dir = 'DESC';
        else $dir = 'ASC';

        // ラベルにアイコンを付ける
        $label .= ' <i class="fa fa-sort-amount-'. strtolower($curr_dir) .'" aria-hidden="true"></i>';
    } else {
        $dir = 'ASC';
    }
    $curr_url = uri_string();

    $req = $CI->input->method()=='get' ? $CI->input->get() : $CI->input->post();
    $get_params = [];
    if (!empty($req)) {
        foreach ($req as $key => $value) {
            if (in_array($key, ['sort','direction','offset'])) continue;
            $get_params[$key] = $value;
        }
    }
    $get_params['sort'] = $sort_key;
    $get_params['direction'] = $dir;

    $url = base_url(uri_string()). '?'.http_build_query($get_params);
    $link = '<a href="'.$url.'" '. _attributes_to_string($attributes) .'>'.$label.'</a>';
    return $link;
}

function limit_link($limit, $label, $attributes=[], $type='link')
{

    $CI = get_instance();
    $sort_key = h($CI->input->get_post('sort'));
    $sort_dir = strtoupper(h($CI->input->get_post('direction')));
    $curr_limit = h($CI->input->get_post('limit'));

    $curr_url = uri_string();

    $req = $CI->input->method()=='get' ? $CI->input->get() : $CI->input->post();
    $get_params = [];
    if (!empty($req)) {
        foreach ($req as $key => $value) {
            if (in_array($key, ['offset','limit'])) continue;
            $get_params[$key] = $value;
        }
    }
    $get_params['sort'] = $sort_key;
    $get_params['direction'] = $sort_dir;
    $get_params['limit'] = $limit;

    if (empty($attributes['class'])) $attributes['class'] = '';
    $attributes['class'] .= ' nav-link';
    if ($curr_limit == $limit || (empty($curr_limit)&&$limit==-1) ) { // 現在指定されているの表示件数と一致
        if($type === 'link'){
            $attributes['class'] .= ' disabled';
        }
        else if($type === 'option'){
            $attributes['selected'] = 'selected';
        }
    }
    $url = base_url(uri_string()). '?'.http_build_query($get_params);
    if($type === 'link'){
        $link = '<a href="'.$url.'" '. _attributes_to_string($attributes) .'>'.h($label).'</a>';
    }
    else if($type === 'option'){
        $link = '<option value="'.$url.'" '. _attributes_to_string($attributes) .'>'.h($label).'</option>';
    }
    else {
        $link = '';
    }

    return $link;
}
// // limitリンク
// // $sort_key: ソートのキー名(実際のソート条件はmodelの$_search_sortsに記述)
// // $label: 表示ラベル名
// // $attributes: aタグに付加する属性名
// function limit_link($limit, $label, $attributes=[])
// {
// 
//     $CI = get_instance();
//     $sort_key = h($CI->input->get_post('sort'));
//     $sort_dir = strtoupper(h($CI->input->get_post('direction')));
//     $curr_limit = h($CI->input->get_post('limit'));
// 
//     $curr_url = uri_string();
// 
//     $req = $CI->input->method()=='get' ? $CI->input->get() : $CI->input->post();
//     $get_params = [];
//     if (!empty($req)) {
//         foreach ($req as $key => $value) {
//             if (in_array($key, ['offset','limit'])) continue;
//             $get_params[$key] = $value;
//         }
//     }
//     $get_params['sort'] = $sort_key;
//     $get_params['direction'] = $sort_dir;
//     $get_params['limit'] = $limit;
// 
//     if (empty($attributes['class'])) $attributes['class'] = '';
//     $attributes['class'] .= ' nav-link';
//     if ($curr_limit == $limit || (empty($curr_limit)&&$limit==-1) ) { // 現在指定されているの表示件数と一致
//         $attributes['class'] .= ' disabled';
//     }
//     $url = base_url(uri_string()). '?'.http_build_query($get_params);
//     $link = '<a href="'.$url.'" '. _attributes_to_string($attributes) .'>'.h($label).'</a>';
// 
//     return $link;
// }
