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
