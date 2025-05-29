<?php /* 編集画面と確認画面共通のテンプレート */ ?>
<div class="edit-box">
    <h3>基本設定</h3>

    <?php if (!empty($model_id)): // 編集時のみ?>
    <div class="mb-2">
        <?php echo admin_form_checkbox_raw('edit_password', [1=>'パスワードを変更する']) ?>
    </div>
    <?php endif?>
    <?php 
    // chromeのautocomplete対策
    // display: noneやvisibility: hidden にしても無視されるため、
    // 透明度を極限まで下げて対応
    echo admin_form_password_raw(AUTH_PASSWORD_FIELD.'_xxxx', null, ['wrap_class'=>'password', 'autocomplete'=>'off', 'style'=>'opacity: 0.001; position: absolute; pointer-events: none; top: -100px; left: 0; z-index:-1;']);?>
    <table class="table table-bordered dataTable">
        <tbody>
            <?php echo admin_form_row(!empty($model_id)?$model_id:'(自動で採番されます)', 'ID', null, 'col-lg-12');?>
            <?php
            if (!empty($this->my_session->admin->flg_admin)){ // 管理権限あり
                echo admin_form_input(AUTH_USER_FIELD, null, ['autocomplete'=>'off'], 'col-lg-12');
            } else { // 管理者権限なし
                $col = admin_form_hidden_raw(AUTH_USER_FIELD,       $this->my_session->admin->login_name);
                $col .= $this->my_session->admin->login_name;
                echo admin_form_row($col, AUTH_USER_FIELD);
            }
            ?>
            <?php echo admin_form_password(AUTH_PASSWORD_FIELD, null, ['wrap_class'=>'password', 'autocomplete'=>'off', 'required' => true], 'col-lg-12');?>
            <?php
            if (!$confirm_flag) { // 入力画面のみ表示
                echo admin_form_password(AUTH_PASSWORD_FIELD.'_confirm', null, ['wrap_class'=>'password', 'autocomplete'=>'off', 'required' => true], 'col-lg-12');
            }
            ?>
            <?php
            if (!empty($this->my_session->admin->flg_admin)){ // 管理権限あり
                $col = admin_form_radio_raw('flg_admin', UserConf::$flg_admin_options, DEFAULT_USER_FLG_ADMIN, ['empty'=>false, 'inline'=>true]);
            } else { // 管理権限なし
                $col = opt($this->my_session->admin->flg_admin, UserConf::$flg_admin_options);
            }
            echo admin_form_row($col, 'flg_admin', ['inline'=>true], 'col-lg-12');
            ?>
            <?php
            if (!empty($this->my_session->admin->flg_admin)){ // 管理権限あり
                echo admin_form_checkbox('flg_acl[]', UserConf::$perm_options, null, ['inline'=>true, 'notice'=>'権限が管理者の場合は、この設定に関わらず全ての操作が許可されます'], 'col-lg-12');
            }?>
            <?php echo admin_form_input('name', null, null, 'col-lg-12');?>
            <?php echo admin_form_input(AUTH_EMAIL_FIELD, null, null, 'col-lg-12');?>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
</div>
<!-- //.edit-box -->

<?php
if (empty($confirm_flag)) echo admin_input_buttons(['preview'=>false, 'draft'=>false]);
else echo admin_confirm_buttons();
?>

<script>
var new_data = <?php echo empty($model_id) ? 1 : 0?>;
$pw_rows = $('table tr.password');
$pw_check = $('input[name="edit_password"]').filter('[type="checkbox"]');
$pw_check.bind('change', function(){
    _pw();
});
_pw(<?php echo !empty($confirm_flag) && !empty($model_id) && !empty($_POST['edit_password']); // 確認画面、編集操作かつパスワード変更が有効な場合 ?>);
function _pw(force){
    if(force || new_data || $pw_check.prop('checked')) $pw_rows.show();
    else $pw_rows.hide();
}

var $admin = $('input[name="flg_admin"]');
var $perms = $('input[name="flg_acl[]"]');
$admin.bind('change', function(){
    on_admin_change();
});
function on_admin_change() {
    var val = $admin.filter(':checked').val();
    if (val === '<?php echo USER_FLG_ADMIN_ADMIN?>') {
        // 管理者
        $perms.prop('disabled', true);
        $perms.prop('checked', true);
    } else {
        $perms.prop('disabled', false);
    }
}
on_admin_change();

</script>
