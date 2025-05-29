<?php if (!empty($model_id) && !empty($_POST['created'])): // 記事編集時?>
<div class="edit-box info_block">
    <table>
        <tbody>
            <?php echo admin_form_row(@$_POST['modified'], '最終更新日時');?>
            <?php echo admin_form_row(@$_POST['created'], '作成日時');?>
        </tbody>
    </table>
    <?php echo admin_form_hidden_raw('modified');?>
    <?php echo admin_form_hidden_raw('created');?>
</div>
<?php endif?>

<?php if( !empty($current_data['is_temporary']) ){ ?>
<div class="p-3 bg-info -temporary" style='font-weight:600;'>
    この記事は一時保存状態です。保存すると公開記事と差し替えします。
<small><a href="javascript:void(0);" onclick="var ok=confirm('一時保存の内容をクリアします');
if (ok) location.href='<?= admin_base_url("$controller/clear_temporary/". $current_data['id']) ?>'; 
return false;"> >>>もとに戻す</a></small></div>
<?php } ?>


