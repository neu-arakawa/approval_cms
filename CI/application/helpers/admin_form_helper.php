<?php

// 編集画面tr要素の出力
// $element_tag: TDセルの中に含む要素タグ
// $label: THセルの中に含むラベル
// $settings: 設定配列
//   required: 必須かどうか。指定がなければ、各フィールドごとに必須ルールの有無で自動判別
//   wrap_class: trに設定するクラス
//   inline: インラインクラス（チェックボックス、ラジオボタンを横並びにする）
//   before_input: 入力要素の前に挿入する要素
//   after_input: 入力要素の後に挿入する要素
//   notice: 入力要素の下に挿入する注意文
// $col_class: bootstrap colクラス名
function admin_form_row($element_tag = '', $label = '', $settings = [], $col_class='col-auto')
{
    $CI = get_instance();

    if ($CI->get_confirm_flag()) { // 確認画面モード
        // 確認画面のときは必須マークを付けない
        if (!empty($settings['required'])) unset($settings['required']);
    }

    $out = sprintf('<tr class="%s %s %s">',
        !empty($settings['required']) ? 'required' : '',
        !empty($settings['wrap_class']) ? $settings['wrap_class'] : '',
        !empty($settings['inline']) ? 'inline' : ''
    );

    $label = lang($label); // ラベル定義参照

    $out .= '<th>'. $label.'</th>';
    $out .= '<td>';
    $out .= '<div class="form-row align-items-center">';
    if (!empty($settings['before_input'])) $out .= '<span class="pl-1">'.$settings['before_input']. '</span>';
    $out .= sprintf('<div class="%s">', $col_class);
    $out .= $element_tag;
    $out .= '</div>';
    if (!empty($settings['after_input'])) $out .= '<span>'. $settings['after_input']. '</span>';
    if (!$CI->get_confirm_flag() && !empty($settings['notice'])) $out .= '<div class="col-md-12"><small class="form-text text-muted">'.$settings['notice'].'</small></div>';
    $out .= '</div>';
    $out .= '</td>';
    $out .= '</tr>';

    return $out;
}

// input type="text" の出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_input($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) { // 確認画面モード
        $col_class = 'col-auto';
    }
    $tag .= admin_form_input_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_input_raw($field, $value='', $settings=[])
{
    $tag = '';
    $CI = get_instance();
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $raw_value = set_value($field, $value, false);
        $tag .= h($raw_value);
        // $tag .= admin_form_hidden_raw($field, $raw_value);
    } else {
        _add_bootstrap_class($settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= my_form_input($field, $value, $settings);
    }
    return $tag;
}


// input type="password" の出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_password($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) { // 確認画面モード
        $col_class = 'col-auto';
    }
    $tag .= admin_form_password_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_password_raw($field, $value='', $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $raw_value = set_value($field, $value, false);
        $tag .= '(セキュリティ上、表示していません)';
        // $tag .= admin_form_hidden_raw($field, $raw_value);
    } else {
        _add_bootstrap_class($settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= my_form_password($field, $value, $settings);
    }
    return $tag;
}

// textareaのタグ出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_textarea($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) { // 確認画面モード
        $col_class = 'col-auto'; // 確認画面時は幅をオートにする
    }
    $tag .= admin_form_textarea_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_textarea_raw($field, $value='', $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $raw_value = set_value($field, $value, false);
        $tag .= nl2br(h($raw_value));
        // $tag .= admin_form_hidden_raw($field, $raw_value);
    } else {
        _add_bootstrap_class($settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= my_form_textarea($field, $value, $settings);
    }
    return $tag;
}

// selectのタグ出力
// $fjield: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_dropdown($field, $options = [], $selected = [], $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) { // 確認画面
        $col_class = 'col-auto';
    }
    $tag .= admin_form_dropdown_raw($field, $options, $selected, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_dropdown_raw($field, $options=[], $selected=[], $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面

        if ($CI->input->post($field) !== null) {
            $selected = $CI->input->post($field);
        }
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        $inline = !empty($settings['inline']) ? ' inline' : '';
        $options = array_flatten($options);
        $tag .= '<ul class="list-value'.$inline.'">';
        foreach ($selected as $s) {
            if (empty($s)) continue;
            if (!empty($options[$s])) {
                $tag .= '<li>';
                $tag .= h($options[$s]);
                $tag .= '</li>';
            } else {
                // $tag .= h($s);
                continue;
            }
            // $tag .= admin_form_hidden_raw($field, $s);
        }
        $tag .= '</ul>';
    } else {

        // $settings['empty'] === false : 空リスト無効
        // $settings['empty'] === (任意の文字列)：空リスト文字列指定
        $empty = '選択してください';
        if (isset($settings['empty'])){
            if ($settings['empty']===false) $empty = null;
            else if (is_string($settings['empty'])) $empty = $settings['empty'];
            unset($settings['empty']);
        }
        if ($empty) {
            $options = [''=>$empty] + $options;
        }

        if (preg_match('/\[\]$/', $field)) $settings['multiple'] = true; // フィールド名の末尾が[]なら自動的にmultipleにする
        _add_bootstrap_class($settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        return my_form_dropdown($field, $options, $selected, $settings);
    }
    return $tag;
}

// input type="checkbox"のタグ出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_checkbox($field, $options = [], $value = null, $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $_field = $field;
    if (preg_match('/^(.+?)\[\]$/', $field, $m)) {
        $_field = $m[1];
    }
    $label = lang($_field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) {
        $col_class = 'col-auto';
    }
    $tag .= admin_form_checkbox_raw($field, $options, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_checkbox_raw($field, $options=[], $value=null, $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) {
        if ($CI->input->post($field) !== null) {
            $value = $CI->input->post($field);
        }
        if (!is_array($value)) $value = [$value];

        $inline = !empty($settings['inline']) ? ' inline' : '';
        $options = array_flatten($options);
        $tag .= '<ul class="list-value'.$inline.'">';
        foreach ($value as $s) {
            if (empty($s)) continue;
            if (!empty($options[$s])) {
                $tag .= '<li>';
                $tag .= h($options[$s]);
                $tag .= '</li>';
            } else {
                continue;
            }
            // $tag .= admin_form_hidden_raw($field, $s);
        }
        $tag .= '</ul>';
    } else {
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= my_form_checkboxes($field, $options, $value, $settings);
    }
    return $tag;
}

// input type="radio"のタグ出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_radio($field, $options = [], $value = null, $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) {
        $col_class = 'col-auto';
    }
    $tag .= admin_form_radio_raw($field, $options, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_radio_raw($field, $options=[], $value=null, $settings=[])
{
    // $settings['empty'] === false : 空リスト無効
    // $settings['empty'] === (任意の文字列)：空リスト文字列指定
    $empty = '選択なし';
    if (isset($settings['empty'])){
        if ($settings['empty']===false) $empty = null;
        else if (is_string($settings['empty'])) $empty = $settings['empty'];
        unset($settings['empty']);
    }
    if ($empty) {
        $options = [''=>$empty] + $options;
    }

    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) {
        if ($CI->input->post($field) !== null) {
            $value = $CI->input->post($field);
        }

        if (!empty($options[$value])) {
            $tag .= h($options[$value]);
        } else if (!empty($value)) {
            $tag .= h($value);
        }
        // $tag .= admin_form_hidden_raw($field, $value);
    } else {
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= my_form_radios($field, $options, $value, $settings);
    }
    return $tag;
}

// ArticleEditor の出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_article_editor($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    $tag .= admin_form_article_editor_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_article_editor_raw($field, $value='', $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $tag .= set_value($field, $value, false);
    } else {
        _add_extra('class', 'article-editor', $settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= admin_form_hidden_raw($field, $value, $settings);
    }
    return $tag;
}

// 画像添付エリア の出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_attach_image($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    $tag .= admin_form_attach_image_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_attach_image_raw($field, $value='', $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $val = set_value($field, $value, false);
        if (empty($val)) return;
        $tag .= '<div class="attach-preview">';
        $tag .= '<img src="'. h($val) . '">';
        $tag .= '</div>';
    } else {
        _add_extra('class', 'attach-upload', $settings);
        _add_extra('data-only-image', '1', $settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= admin_form_hidden_raw($field, $value, $settings);
    }
    return $tag;
}

// ファイル添付エリア の出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_attach_file($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    if ($CI->get_confirm_flag()) { // 確認画面モード
        $val = set_value($field, $value, false);
        $tag .= '<div class="attach-preview">';
        if ( !empty($val) ) {
            $tag .= '<a href="'. h($val). '" target="_blank">'. h($val) .'</a>';
        }
        else {
            $tag .= '（未選択）';
        }
        $tag .= '</div>';
    } else {
        $tag .= admin_form_attach_file_raw($field, $value, $settings);
    }
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_attach_file_raw($field, $value='', $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $val = set_value($field, $value, false);
        if (empty($val)) return;
        $tag .= '<div class="attach-preview">';
        $tag .= '<a href="'. h($val). '" target="_blank">'. h($val) .'</a>';
        $tag .= '</div>';
    } else {
        _add_extra('class', 'attach-upload', $settings);
        _add_extra('data-only-image', '0', $settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= admin_form_hidden_raw($field, $value, $settings);
    }
    return $tag;
}

// インラインエディター の出力
// $field: フィールド名
// $value: フィールドの値
// $settings: 設定配列
// $col_class: bootstrap colクラス名
function admin_form_inline_editor($field, $value = '', $settings = [], $col_class = 'col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定
    $settings['data-required'] = $required; // 必須属性を付与

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    }

    $tag .= admin_form_inline_editor_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}
function admin_form_inline_editor_raw($field, $value='', $settings=[])
{
    $CI = get_instance();
    $tag = '';
    if ($CI->get_confirm_flag()) { // 確認画面モード
        $tag .= set_value($field, $value, false);
    } else {
        _add_extra('class', 'inline-editor', $settings);
        if (!_is_validated($field)) _add_extra('class', 'is-invalid', $settings); // エラーの場合はクラス追加
        $tag .= admin_form_hidden_raw($field, $value, $settings);
    }
    return $tag;
}

function admin_form_hidden($field, $value = '', $settings = [], $col_class='col-auto')
{
    $CI = get_instance();
    if (empty($settings)) $settings = [];
    $row_set = _get_row_class($settings);   // admin_form_row用のデータ切り出し

    $tag = '';
    $required = _is_required($field); // 必須判定

    // ラベル取得
    $label = lang($field);
    if (!empty($settings['label'])) {
        $label = $settings['label'];
        unset($settings['label']);
    } else

    if ($CI->get_confirm_flag()) { // 確認画面モード
        $col_class = 'col-auto';
    } else {
        $settings['data-required'] = $required; // 必須属性を付与
    }
    $tag .= admin_form_hidden_raw($field, $value, $settings);
    return admin_form_row($tag, $label, $row_set, $col_class);
}

function admin_form_hidden_raw ($field, $value = '', $settings = []) {

    $settings['type'] = 'hidden';
    $settings['name'] = $field;
    $settings['id'] = $field;

    $form = '';
    if (!is_array($value)) { // 値がスカラ値の場合
        $settings['value'] = set_value($field, $value);
        $form .= '<input '._attributes_to_string($settings).'/>'."\n";
    } else { // 値が配列値の場合
        foreach ($value as $k => $v) {
            $k = is_int($k) ? '' : $k;
            $form .= admin_form_hidden_raw($field.'['.$k.']', $v);
        }
    }
    return $form;
}

// 検索用行要素の出力
// $element_tag: TDセルの中に含む要素タグ
// $label: THセルの中に含むラベル
// $settings: 設定配列
//   wrap_class: trに設定するクラス
//   inline: インラインクラス（チェックボックス、ラジオボタンを横並びにする）
//   before_input: 入力要素の前に挿入する要素
//   after_input: 入力要素の後に挿入する要素
//   notice: 入力要素の下に挿入する注意文
// $col_class: bootstrap colクラス名
function admin_search_row($element_tag = '', $label = '', $settings = [], $col_class='col-auto')
{
    $out = sprintf('<div class="form-row align-items-top %s %s">',
        !empty($settings['wrap_class']) ? $settings['wrap_class'] : '',
        !empty($settings['inline']) ? 'inline' : ''
    );
    $label = lang($label); // ラベル定義参照

    $out .= '<div class="col-md-2"><label class="col-form-label">'. $label.'</label></div>';
    $out .= '<div class="col-md-10">';
    $out .= '<div class="row align-items-center">';

    if (!empty($settings['before_input'])) $out .= '<span class="pl-1">'.$settings['before_input']. '</span>';
    $out .= sprintf('<div class="%s">', $col_class);
    $out .= $element_tag;
    $out .= '</div>';
    if (!empty($settings['after_input'])) $out .= '<span>'. $settings['after_input']. '</span>';
    if (!empty($settings['notice'])) $out .= '<div class="col-md-12"><small class="form-text text-muted">'.$settings['notice'].'</small></div>';

    $out .= '</div>';
    $out .= '</div>';
    $out .= '</div>';

    return $out;
}

// bootstrapクラス追加
function _add_bootstrap_class(&$settings)
{
    $bootstrap_class = 'form-control';
    _add_extra('class', 'form-control', $settings);
}

// 必須かどうか
function _is_required($field)
{
    $CI = get_instance();
    return $CI->form_validation->is_required_field($field);
}

// バリデート結果取得
function _is_validated($field)
{
    if( ($v = _get_validation_object()) !== false ) {
        return !$v->error($field);
    }
    return true;
}

// 属性配列へのデータ追加
function _add_extra($key, $val, &$settings)
{
    if (empty($settings)) $settings = [];
    if (empty($settings[$key])) $settings[$key] = $val;
    else $settings[$key] .= ' '. $val;
}

// 要素を包むための要素(admin_form_row())に必要な
// 設定値をextraからのぞき、切り出した配列を返す
function _get_row_class(&$settings)
{
    $keys = [
        'required', 'wrap_class', 'inline', 'before_input', 'after_input', 'notice',
    ];
    $out = [];
    foreach ($keys as $k) {
        if (isset($settings[$k])) {
            $out[$k] = $settings[$k];
            unset($settings[$k]);
        }
    }
    return $out;
}

// パンくずリストの出力
function bread_crumbs()
{
    $args = func_get_args();

    $out = '<div class="col-lg-5">';
    $out .= '<nav aria-label="breadcrumb" role="navigation">';
    $out .= '<ol class="breadcrumb">';

    $out .= '<li class="breadcrumb-item">';
    $out .= '<a href="'.admin_base_url(). '"><i class="fas fa-home"></i></a>';
    $out .= '</li>';

    if (!empty($args) && is_array($args)) {
        foreach ($args as $d) {
            $out .= '<li class="breadcrumb-item">';
            if (!empty($d['url'])) $out .= '<a href="'.admin_base_url($d['url']). '">'. $d['label'] .'</a>';
            else $out .= $d['label'];
            $out .= '</li>';
        }
    }

    $out .= '</ol>';
    $out .= '</nav>';
    $out .= '</div>';
    return $out;
}

// 入力ボタン群出力
// $settings: 設定配列
function admin_input_buttons($settings = [])
{
    $default = [
        'cancel'    => 'キャンセル',
        'reject'    => '差し戻す',
        'approved'  => '承認して公開する',
        'pending_midflow'   => false,
        'draft'     => '下書き保存',
        'direct'    => '登録する',
        'pending'   => false, //'承認依頼として送信する',
        'confirm'   => false,
        'preview'   => 'プレビュー',
    ];
    $settings = array_merge($default, $settings);

    $out = '<div class="row edit-buttons">';
    $out .= '<div class="col-sm-12">';
    if (!empty($settings['cancel'])) { // キャンセルボタン
        $out .= '<button type="submit" name="btn_cancel" class="btn btn-danger" onclick="return confirm(\'入力内容を破棄します。\nよろしいですか?\');">'. $settings['cancel'] .'</button>';
    }

    if (!empty($settings['pending_midflow'])) { // 保留ボタン
        $out .= '<button type="submit" name="btn_pending_midflow" class="btn pull-right">';
        $out .= '<i class="fa fa-floppy-o" aria-hidden="true"></i> '.$settings['pending_midflow'];
        $out .= '</button>';
    }

    if (!empty($settings['reject'])) { // 差し戻しボタン
        $out .= '<button type="button" name="btn_reject" class="btn btn-danger pull-right mr-1" data-confirm="<h5>承認NGにしますか？</h5><small class=\'red\'>※ この操作は取り消しできません</small>">';
        $out .= '<i class="fa fa-ban" aria-hidden="true"></i> '.$settings['reject'];
        $out .= '</button>';
    }

    if (!empty($settings['draft'])) { // 下書きボタン
        $out .= '<button type="submit" name="btn_draft" class="btn btn-secondary pull-right mr-1">';
        $out .= '<i class="fa fa-floppy-o" aria-hidden="true"></i> '. $settings['draft'];
        $out .= '</button>';
    }


    if (!empty($settings['approved'])) { // 承認してボタン
        $out .= '<button type="button" name="btn_approved" class="btn btn-primary pull-right mr-1" data-confirm="<h5>承認OKにしますか？</h5><small class=\'red\'>※ この操作は取り消しできません</small>">';
        $out .= '<i class="fa fa-check"></i> '.$settings['approved'];
        $out .= '</button>';
    }

    if (!empty($settings['confirm'])) { // 確認ボタン
        $out .= '<button type="submit" name="btn_confirm" class="btn btn-primary pull-right mr-0">';
        $out .= $settings['confirm'];
        $out .= '</button>';
    }

    if (!empty($settings['direct'])) { // 確認無しで保存
        $out .= '<button type="submit" name="btn_direct" class="btn btn-primary pull-right mr-1">';
        $out .= '<i class="fa fa-floppy-o" aria-hidden="true"></i> '.$settings['direct'];
        $out .= '</button>';
    }


    if (!empty($settings['pending'])) { // 承認依頼ボタン
        $out .= '<button type="button" name="btn_pending" class="btn btn-primary pull-right mr-1" data-confirm="<h5>承認依頼しますか？</h5>">';
        $out .= '<i class="fa fa-paper-plane" aria-hidden="true"></i> '.$settings['pending'];
        $out .= '</button>';
    }

    if (!empty($settings['preview'])) { // プレビュー
        $out .= '<button type="button" name="btn_preview" class="btn btn-warning pull-right mr-1" id="preview">';
        $out .= '<i class="fa fa-eye" aria-hidden="true"></i> '. $settings['preview'];
        $out .= '</button>';
    }


    $out .= '</div>';
    $out .= '</div>';
    return $out;
}

// 確認ボタン群出力
// $settings: 設定配列
function admin_confirm_buttons($settings = [])
{
    $default = [
        'back' => '入力画面に戻る',
        'send' => '登録する',
    ];
    $settings = array_merge($default, $settings);

    $out = '<div class="row edit-buttons">';
    $out .= '<div class="col-sm-12">';
    if (!empty($settings['back'])) { // 戻るボタン
        $out .= '<button type="submit" name="btn_back" class="btn btn-danger">';
        $out .= $settings['back'];
        $out .= '</button>';
    }
    if (!empty($settings['send'])) {
        $out .= '<button type="submit" name="btn_send" class="btn btn-primary pull-right">';
        $out .= $settings['send'];
        $out .= '</button>';
    }
    $out .= '</div>';
    $out .= '</div>';
    return $out;
}

// 一覧検索ボタン群出力
// $settings: 設定配列
function admin_search_buttons($settings=[])
{
    $reset_url = '';
    if (empty($settings['reset_url'])) { // リセット用のURLが指定されていない
        $reset_url = base_url(uri_string());

        // ソート条件の引き継ぎ
        $CI = get_instance();
        $sort_key = $CI->input->get_post('sort');
        $sort_dir = $CI->input->get_post('direction');
        $limit = $CI->input->get_post('limit');

        $queries = [];
        // if (!empty($sort_key)) $queries['sort'] = $sort_key;
        // if (!empty($sort_dir)) $queries['direction'] = $sort_dir;
        // if (!empty($limit)) $queries['limit'] = $limit;
        $queries['1'] = 1;
        if (!empty($queries)) {
            $reset_url .= '?'. http_build_query($queries);
        }
    } else { // URL指定あり
        $reset_url = $settings['reset_url'];
    }

    $out = '<div class="search-buttons">';
    $out .= '<div class="buttons-left">';
    $out .= '<a class="btn btn-xs text-secondary" onclick="location.href=\''.h($reset_url).'\'"><i class="fas fa-times"></i> 検索条件を解除</small></a>';
    $out .= '</div>';
    $out .= '<div class="buttons-right">';
    $out .= '<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> この条件で検索</button>';
    $out .= '</div>';
    $out .= admin_form_hidden_raw('sort');
    $out .= admin_form_hidden_raw('direction');
    $out .= admin_form_hidden_raw('limit');
    $out .= '</div>';

    return $out;
}

// 一覧上のメニュー出力
// $id: 対象となるデータID
function admin_list_menu($id, $settings = [])
{
    $CI = get_instance();
    $controller = $CI->router->class; // コントローラ名を取得

    $default = [
        // 編集ボタン
        'edit' => true,
        'edit_label' => '<i class="fas fa-pencil-alt"></i> 編集',
        'edit_url' => admin_base_url("{$controller}/edit/{$id}"),

        // 複製ボタン
        'replicate' => true,
        'replicate_label' => '<i class="far fa-copy"></i> 複製',
        'replicate_url' => admin_base_url("{$controller}/replicate/{$id}"),
        'replicate_confirm' => "記事 (ID: {$id}) を複製します。よろしいですか？",

        // 削除ボタン
        'delete' => true,
        'delete_label' => '<i class="far fa-trash-alt"></i> 削除',
        'delete_url' => admin_base_url("{$controller}/delete/{$id}"),
        'delete_confirm' => "記事 (ID: {$id}) を削除します。よろしいですか？",

        // 公開ボタン
        'publish' => false,      // 公開ボタンだけは true / false の他に "disabled" を設定可(下書き状態だが、バリデートが完了していない場合で公開ができない場合を想定)
        'publish_label' => '<i class="far fa-eye"></i> 公開',
        'publish_url' => admin_base_url("{$controller}/publish/{$id}/1"),
        'publish_confirm' => "記事 (ID: {$id}) を公開します。よろしいですか？",

        // 下書きボタン
        'draft' => false,
        'draft_label' => '<i class="far fa-eye-slash"></i> 公開取下げ',
        'draft_url' => admin_base_url("{$controller}/publish/{$id}/0"),
        'draft_confirm' => "記事 (ID: {$id}) を公開取下げにします。よろしいですか？",

        // 任意ボタン
        'other' => [
            // [
            //     'insert_to' => 0,          // ドロップダウン挿入位置
            //     'label' => 'label',
            //     'url' => 'dummy',
            //     'onclick' => '',
            //     'divider' => false,
            // ],
        ],
    ];
    $settings = array_merge($default, $settings);

    $btn_func = function($layout, $name, $idx=0) use ($id, $settings) {
        $tag_name = 'a';
        $style = '';
        $divider = false;
        switch ($name) {
            case 'edit':
                if ($layout == 'inline')  $cls = 'btn btn-primary btn-xs btn-edit';
                else if ($layout == 'dropdown') $cls = 'dropdown-item';

                $onclick = '';

                $label = $settings['edit_label'];
                $url = $settings['edit_url'];
                break;
            case 'replicate':
                if ($layout == 'inline')  $cls = 'btn btn-primary btn-xs btn-replicate';
                else if ($layout == 'dropdown') $cls = 'dropdown-item';

                $onclick = !empty($settings['replicate_confirm']) ?
                            "return confirm('{$settings['replicate_confirm']}')" :
                            '';

                $label = $settings['replicate_label'];
                $url = $settings['replicate_url'];
                break;
            case 'publish':
                if ($layout == 'inline')  $cls = 'btn btn-primary btn-xs btn-publish';
                else if ($layout == 'dropdown') $cls = 'dropdown-item';

                if ($settings['publish'] === 'disabled') {
                    $onclick = 'alert(\'記事 (ID: '.$id.') の入力が完了していないため公開できません。編集メニューから再度記事内容をご確認の上、登録を行ってください。\'); return false;';
                    $cls .= ' disabled';
                    $tag_name = 'span';
                    $style = 'cursor:not-allowed';
                } else {
                    $onclick = !empty($settings['publish_confirm']) ?
                            "return confirm('{$settings['publish_confirm']}')" :
                            '';
                }

                $label = $settings['publish_label'];
                $url = $settings['publish_url'];
                break;
            case 'draft':
                if ($layout == 'inline')  $cls = 'btn btn-primary btn-xs btn-draft';
                else if ($layout == 'dropdown') $cls = 'dropdown-item';

                $onclick = !empty($settings['draft_confirm']) ?
                            "return confirm('{$settings['draft_confirm']}')" :
                            '';

                $label = $settings['draft_label'];
                $url = $settings['draft_url'];
                break;
            case 'delete':
                if ($layout == 'inline') {
                    $cls = 'btn btn-danger btn-xs btn-delete';
                } else if ($layout == 'dropdown') {
                    $cls = 'dropdown-item text-danger';
                    $divider = true;
                }

                $onclick = !empty($settings['delete_confirm']) ?
                            "return confirm('{$settings['delete_confirm']}')" :
                            '';

                $label = $settings['delete_label'];
                $url = $settings['delete_url'];

                break;
            case 'other':
                if ($layout == 'inline')  $cls = 'btn btn-primary btn-xs btn-draft';
                else if ($layout == 'dropdown') $cls = 'dropdown-item';

                $divider = !empty($settings['other'][$idx]['divider']) ? true : false;
                $onclick = !empty($settings['other'][$idx]['onclick']) ? $settings['other'][$idx]['onclick'] : '';
                $label = $settings['other'][$idx]['label'];
                $url = $settings['other'][$idx]['url'];

                break;
            default:
                return;
        }
        $out = '';
        if ($divider) $out .= '<div class="dropdown-divider"></div>';
        $out .= '<'.$tag_name.' class="'.$cls.'" href="'. h($url) .'" style="'.h($style).'" onclick="'.h($onclick).'">'. $label .'</'.$tag_name.'>';
        return $out;
    };

    $out = '';
    if (!empty($settings['edit'])) {
        $out .= $btn_func('inline', 'edit');
    }
    if ($settings['replicate'] || $settings['publish'] || $settings['draft'] || $settings['delete'] || $settings['other']) {

        $btns = [];
        $curr_idx = 0;

        foreach (['replicate', 'publish', 'draft', 'delete', 'other'] as $name) {
            if (!empty($settings[$name])) {

                // 指定の位置に挿入する「その他」ボタンがないか調べる
                if (!empty($settings['other'])) {
                    foreach ($settings['other'] as $idx => $arr) {
                        if (isset($arr['insert_to']) && $arr['insert_to']==$curr_idx) {
                            // 挿入対象のボタンを発見
                            $btns['other_'.$idx] = $btn_func('dropdown', 'other', $idx);
                            $settings['other'][$idx]['done'] = true;
                        }
                    }
                }

                if ($name=='other') {
                    for ($i=0; $i<count($settings['other']); $i++) {
                        if (!empty($settings['other'][$i]['done'])) {
                            // すでに表示済みのため、スキップ
                            continue;
                        }
                        $btns['other_'.$i] = $btn_func('dropdown', 'other', $i);
                    }
                } else {
                    $btns[$name] = $btn_func('dropdown', $name);
                }
                $curr_idx++;
            }
        }

        if (count($btns)===1) { // 項目が一つの時
            reset($btns);
            $name = key($btns);
            // ドロップダウンではなく、インラインボタンとして表示する
            if (preg_match('/^other_(\d)$/', $name, $m)) {
                // その他ボタン
                $out .= $btn_func('inline', 'other', $m[1]);
            } else {
                $out .= $btn_func('inline', $name);
            }
        } else if (count($btns)>0) {
            $out .= '<button type="button" class="btn dropdown-toggle btn-xs btn-other" data-toggle="dropdown">その他の操作</button><div class="dropdown-menu dropdown-menu-right">'. implode("\n", $btns). '</div>';
        }
    }
    return $out;
}

// モーダルウィンドウの一覧上のメニュー
// $id: データID
// $label: データの見出し
function admin_modal_select_button($id, $label)
{
    return '<button type="button" class="btn btn-primary btn-xs" data-modal-select data-modal-select-id="'.h($id).'" data-modal-select-label="'.$label.'">選択</button>';
}
// モーダルウィンドウを開いてデータを選択するための部品
function admin_modal_target($settings=[]){
    $CI = get_instance();
    $confirm = $CI->get_confirm_flag();

    $default = [
        'label_name'        => null,        // 名前のhidden名
        'id_name'           => null,        // IDのhidden名
        'modal_url'         => null,        // モーダルのURL
        'button'            => '選択',       // 選択ボタンの見出し
    ];
    $settings = array_merge($default, $settings);

    $out = '';

    // データ名を表示するエリア
    $out .= '<span data-modal-label-target></span>';

    $out .= admin_form_hidden_raw(h($settings['id_name']), null, ['data-modal-id-target'=>1]);
    $out .= admin_form_hidden_raw(h($settings['label_name']), null, ['data-modal-label-target'=>1]);
    if (!$confirm) {
        // 確認画面以外
        $out .= '<button type="button" class="btn btn-danger btn-xs" data-modal-action="clear" style="display: none;">クリア</button>';
        $out .= '<button type="button" class="btn btn-secondary btn-xs" data-modal-action="select" data-modal-search-url="'.h($settings['modal_url']).'">'.h($settings['button']).'</button>';
    }

    return $out;
}

// 一括操作のメニュー表示
function admin_batch_menu($settings=[])
{
    $CI = get_instance();
    $controller = $CI->router->class; // コントローラ名を取得
    $default = [
        // 複製ボタン
        'replicate' => true,
        'replicate_label' => '複製',
        'replicate_url' => admin_base_url("{$controller}/replicate_all"),
        'replicate_style' => 'btn-primary',
        'replicate_confirm' => "チェックしている項目をすべて複製します。\nよろしいですか？",

        // 削除ボタン
        'delete' => true,
        'delete_label' => '削除',
        'delete_url' => admin_base_url("{$controller}/delete_all"),
        'delete_style' => 'btn-danger',
        'delete_confirm' => "チェックしている項目をすべて削除します。この処理は取り消せません。\nよろしいですか？",

        // 公開ボタン
        'publish' => true,
        'publish_label' => '公開',
        'publish_url' => admin_base_url("{$controller}/publish_all/1"),
        'publish_style' => 'btn-primary',
        'publish_confirm' => "チェックしている項目をすべて公開します。\nただし、入力が完了していない公開取下げ状態の記事については公開はできないため、可能な項目のみ公開されます。\nよろしいですか？",

        // 下書きボタン
        'draft' => true,
        'draft_label' => '公開取下げ',
        'draft_url' => admin_base_url("{$controller}/publish_all/0"),
        'draft_style' => 'btn-primary',
        'draft_confirm' => "チェックしている項目をすべて公開取下げにします。\nよろしいですか？",
    ];
    $settings = array_merge($default, $settings);

    $out = '<div class="batch-wrap controller-left">';
    $out .= '<div class="input-group input-group-sm">';
    $out .= '<select class="custom-select custom-select-sm">';
    $out .= '<option value="">一括操作</option>';

    foreach (['replicate', 'delete', 'publish', 'draft'] as $name) {
        if (empty($settings[$name])) continue;

        $out .= '<option value="'.h($name).'" data-url="'.$settings[$name.'_url'].'" data-style="'.$settings[$name.'_style'].'" data-confirm="'.h($settings[$name.'_confirm']).'">'.$settings[$name.'_label'] .'</option>';
    }

    $out .= '</select>';
    $out .= '<div class="input-group-append">';
    $out .= '<button class="btn btn-secondary disabled" type="button">実行</button>';
    $out .= '</div>';
    $out .= '</div>';
    $out .= '</div>';

    $script = <<< 'EOM'
    <script>
    $(document).ready(function(){
        var $checkbox = $('input[type="checkbox"].batch');
        var $chk_wrap = $checkbox.closest('td');
        var $chk_all = $('input[type="checkbox"].batch_all');
        var $all_wrap = $chk_all.closest('th');
        var $select = $('.batch-wrap select');
        var $btn = $('.batch-wrap button');

        // 一括操作の選択
        $select.bind('change', function(){
            // ボタンのスタイルを操作に応じて変更する
            var val = $(this).val();
            if (!val) {
                $btn
                    .attr('class', 'btn btn-secondary disabled');
                return false;
            }

            var $opt = $(this).find('option[value="'+val+'"]');
            var url = $opt.attr('data-url');
            var style = $opt.attr('data-style');
            var label = $opt.text();
            if (style) {
                $btn
                    .attr('class', 'btn '+style);
            }
        });

        // 実行ボタンのクリック
        $btn.bind('click', function(){
            var val = $select.val();
            if (!val) {
                alert('一括操作を選択してください');
                return false;
            }
            var $opt = $select.find('option[value="'+val+'"]');
            var url = $opt.attr('data-url');
            var cnf_msg = $opt.attr('data-confirm');

            var $checked = $checkbox.filter(':checked');
            if ($checked.length==0) {
                alert('一括操作対象の項目をチェックしてください');
                return false;
            }
            if (cnf_msg) {
                if (!confirm(cnf_msg)) {
                    // 処理のキャンセル
                    $select
                        .val('')
                        .trigger('change');
                    return false;
                }
            }

            var ids = [];
            $checked.each(function(){
                var id = $(this).val();
                ids.push(id);
            });

            url += '?ids=' + ids.join(',');
            location.href = url;
        });

        // 一括チェックボックス
        $chk_all.bind('change', function(){
            var all_checked = $(this).prop('checked');
            $checkbox.prop('checked', all_checked).change();
        });
        $all_wrap.bind('click',function(ev){
            if (ev.target != $(this).get(0)) return;
            $chk_all.click();
        });

        // チェックボックスを含むセル
        $chk_wrap.bind('click', function(ev){
            if (ev.target != $(this).get(0)) return;
            $(this).find('input[type="checkbox"]').click();
        });
        $checkbox.bind('change', function(){
            var checked = $(this).prop('checked');
            if (checked) {
                $(this).closest('td').addClass('checked');
            } else {
                $(this).closest('td').removeClass('checked');
            }
        });
        $checkbox.change();
    });
    </script>
EOM;

    return $out.$script;
}

// バリデーションエラー出力
function admin_validation_errors($errors = [])
{
    if (($v = _get_validation_object()) === false || empty($v->error_array()) && empty($errors)) {
        return '';
    }

    $msg_tag = '';
    if (!empty($errors)) {
        $msg_tag .= '<li>';
        $msg_tag .= implode("</li>\n<li>", $errors);
        $msg_tag .= '</li>';
    }

    $out  = '';
    $out .= '<div class="alert alert-danger validate">';
    $out .= '<h3><i class="icon fas fa-ban"></i> 下記のエラーがあります</h3>';
    $out .= '<ul>';
    $out .= $msg_tag. $v->error_string('<li>', '</li>');
    $out .= '</ul>';
    $out .= '</div>';

    return $out;
}

// 必須マーク出力
function admin_required_badge()
{
    return '<span class="badge badge-pill required">必須</span>';
}

// プレビュー用URL出力
function admin_preview_url($url, $validated=true)
{
    return base_url('/preview'). '?url='. urlencode($url). '&validated='.$validated;
}

// view アイコン出力
function admin_view_icon($controller=null)
{
    $CI = get_instance();
    if (empty($controller)) {
        // コントローラ名が未指定 or クラス定義なし
        $controller = 'admin/'.$CI->router->class; // 現在のコントローラ名を取得
    }
    $name = $CI->load->load_controller($controller);
    if ($name !== false) {
        return $name::VIEW_ICON;
    }
    return null;
}

// 公開／非公開のラベル
function admin_switch($checked, $settings=[])
{
    $default = [
        'title' => '',
        'on_label' => '公開',
        'off_label' => '下書き',
        'on_url' => null,
        'off_url' => null,
    ];
    $settings = array_merge($default, $settings);
    if (!isset($settings['onclick_js'])) {
        $json = json_encode($settings);
        $settings['onclick_js'] = "
            var msg;
            var settings = {$json};
            if(this.checked){ msg = settings['title'] + ' の状態を 「' + settings['on_label'] + '」に変更します。よろしいですか？'; }
            else{ msg = settings['title'] + ' の状態を 「' + settings['off_label'] + '」に変更します。よろしいですか？'; }
            if(!confirm(msg)){
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
            this.parentNode.querySelector('label').textContent = this.checked ? settings['on_label'] : settings['off_label'];
            if(this.checked && settings['on_url']) location.href = settings['on_url'];
            else if(!this.checked && settings['off_url']) location.href = settings['off_url'];
        ";
    }

    $label = $checked ? $settings['on_label'] : $settings['off_label'];
    $chk_attr = $checked ? 'checked="checked"' : '';

    $out = '';
    $out .= '<div class="pretty p-switch p-fill">';
    $out .= '<input type="checkbox" onclick="'.h($settings['onclick_js']).'" '.$chk_attr.' />';
    $out .= '<div class="state p-primary">';
    $out .= '<label>'.h($label).'</label>';
    $out .= '</div>';
    $out .= '</div>';
    return $out;
}

// ページネーション
function admin_pagination($pager, $options=[])
{
    $default = [
        'limits' => [],
        'limit_enabled' => true,
        'pager_enabled' => true,
        'disp_enabled' => true,
    ];
    $options = array_merge($default, $options);

    // 必要な変数が未定義の場合
    if (empty($pager) || empty($pager['count'])) {
        return;
    }
    if (empty($pager['offset'])) {
        $offset = 0;
    }

    // 何件目を表示しているかの数字
    if ($pager['limit'] === null) { // 全件表示
        $first = 1;
        $last = $pager['count'];
    } else {
        $first = $pager['offset'] + 1;
        $last = $pager['offset'] + $pager['limit'];
        if ($last > $pager['count']) {
            $last = $pager['count'];
        }
    }

    // 表示反映のため、GET/POSTに設定
    $_GET['limit'] = $_POST['limit'] = $pager['limit'];
    if (!empty($pager['order_by'])) {
        $_GET['direction'] = $_POST['direction'] = reset($pager['order_by']);
        $_GET['sort'] = $_POST['sort'] = key($pager['order_by']);
    }

    // paginationの設定
    $config = [];
    $config['total_rows'] = $pager['count'];
    $config['per_page'] = $pager['limit'];

    $config['page_query_string'] = true;
    $config['query_string_segment'] = 'offset';
    $config['reuse_query_string'] = true;

    $config['first_link'] = '';
    $config['first_tag_open'] = '<li class="first">';
    $config['first_tag_close'] = '</li>';

    $config['last_link'] = '';
    $config['last_tag_open'] = '<li class="last">';
    $config['last_tag_close'] = '</li>';

    $config['num_links'] = 2;

    $config['next_link'] = '';
    $config['next_tag_open'] = '<li class="next">';
    $config['next_tag_close'] = '</li>';
    $config['prev_tag_open'] = '<li class="prev">';
    $config['prev_tag_close'] = '</li>';

    $config['cur_tag_open'] = '<li>';
    $config['cur_tag_close'] = '</li>';

    $config['prev_link'] = '';
    $config['num_tag_open'] = '<li>';
    $config['num_tag_close'] = '</li>';

    if (!empty($pager['config'])) {
        $config = array_merge($config, $pager['config']);
    }

    $CI = get_instance();
    $CI->pagination->initialize($config);

    $out = '';
    $out .= '<div class="paginator-wrap controller-right">';

    if ($options['pager_enabled']) {
        $out .= '<div class="numbers">';
        $out .= '<nav><ul class="pagination pagination-sm">';
        $link = $CI->pagination->create_links();

        // 最初へリンク
        if (preg_match('/<li class="first">(.+?)<\/li>/', $link, $m, PREG_OFFSET_CAPTURE)) {
            $href = '';
            if (preg_match('/href="(.+?)"/', $m[0][0], $m2)) {
                $href = $m2[1];
            }
            if ($href) {
                $replace = '<li class="first page-item"><a class="page-link" href="'.$href.'" rel="first"><i class="fas fa-chevron-left"></i> 最初へ</a></li>';
                $link = substr_replace($link, $replace, $m[0][1], strlen($m[0][0]));
            }
        }

        // 最後へリンク
        if (preg_match('/<li class="last">(.+?)<\/li>/', $link, $m, PREG_OFFSET_CAPTURE)) {
            $href = '';
            if (preg_match('/href="(.+?)"/', $m[0][0], $m2)) {
                $href = $m2[1];
            }
            if ($href) {
                $replace = '<li class="last page-item"><a class="page-link" href="'.$href.'" rel="last">最後へ <i class="fas fa-chevron-right"></i></a></li>';
                $link = substr_replace($link, $replace, $m[0][1], strlen($m[0][0]));
            }
        }

        // 前へリンク
        if (preg_match('/<li class="prev">(.+?)<\/li>/', $link, $m, PREG_OFFSET_CAPTURE)) {
            $href = '';
            if (preg_match('/href="(.+?)"/', $m[0][0], $m2)) {
                $href = $m2[1];
            }
            if ($href) {
                $replace = '<li class="back page-item"><a class="page-link" href="'.$href.'" rel="prev"><i class="fas fa-chevron-left"></i> 前へ</a></li>';
                $link = substr_replace($link, $replace, $m[0][1], strlen($m[0][0]));
            }
        }
        // 次へリンク
        if (preg_match('/<li class="next">(.+?)<\/li>/', $link, $m, PREG_OFFSET_CAPTURE)) {
            $href = '';
            if (preg_match('/href="(.+?)"/', $m[0][0], $m2)) {
                $href = $m2[1];
            }
            $replace = '<li class="next page-item"><a class="page-link" href="'.$href.'" rel="next">次へ <i class="fas fa-chevron-right"></i></a></li>';
            $link = substr_replace($link, $replace, $m[0][1], strlen($m[0][0]));
        }
        // ページリンク
        if (preg_match_all('/<li>(.+?)<\/li>/', $link, $matched, PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) {
            $matched = array_reverse($matched);
            foreach ($matched as $m) {
                $pos = $m[0][1];
                $len = strlen($m[0][0]);
                $page = $m[1][0];
                $href = '';
                if (preg_match('/<a\s+href="(.+?)"[^>]*>(.+?)<\/a>/', $m[0][0], $m2)) {
                    $href = $m2[1];
                    $page = $m2[2];
                }
                if ($href) {
                    $replace = '<li class="number page-item "><a class="page-link" href="'.$href.'">'.$page.'</a></li>';
                } else {
                    $replace = '<li class="number page-item active"><a class="page-link" href="#">'.$page.'</a></li>';
                }
                $link = substr_replace($link, $replace, $pos, $len);
            }
        }
        $out .= $link;

        $out .= '</ul></nav>';
        $out .= '</div>';// .numbers
    }

    $out .= '<div class="disp-wrap">';

    if ($options['disp_enabled']) {
        $out .= '<div class="disp-num">';
        $out .= h($pager['count']). '<small>件中</small>';
        if ($first==$last) $out .= h($first). '<small>件目を表示</small>';
        else $out .= h($first). '<small>〜</small>'. h($last). '<small>件目を表示</small>';
        $out .= '</div>';
    }

    if ($options['limit_enabled']) {

        $out .= '<div class="limit">';
        $out .= '<select class="custom-select" onChange="location.href=value;">';

        foreach ($options['limits'] as $d) {
            if ($d<0) $label = '全件';
            else $label = $d.'件';
            $out .= limit_link($d, $label,[],'option');
        }
        $out .= '</select>';

        // $out .= '<div class="limit">';
        // if (!empty($options['limits'])){
            // $out .= '<ul class="nav">';
            // foreach ($options['limits'] as $d) {
                // if ($d<0) $label = '全件';
                // else $label = $d.'件';

                // $out .= '<li class="nav-item">'. limit_link($d, $label). '</li>';
            // }
            // $out .= '</ul>';
        // }
        $out .= '</div>';
    }

    $out .= '</div>';

    $out .= '</div>'; // .paginator-wrap

    return $out;
}

//承認用ステータス
function admin_post_status_for_approval($data){
    $out = '';
    if( $data['status'] == APPROVAL_STATUS_PUBLISHED ){
        if (!empty($data['published'])) { 
            echo '公開中';
        }
        elseif($data['future']){
            echo '公開予約';
            echo "<div class='future_date'>";
            echo h($data['start_date'] );
            echo '</div>';
        }
        else {
            echo '非公開';
        }
    }
    else {
        echo '<span class="badge badge-danger" style="font-size:100%;padding:8px;">';
        echo opt($data['status'] , ApprovalConf::$status_options);
        echo '</span>';
    }
    return $out;
}

