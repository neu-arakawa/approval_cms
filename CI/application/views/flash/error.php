<?php /* エラー時のフラッシュメッセージ */?>
<div class="alert alert-danger <?php if($dismiss) echo 'alert-dismissible'?>">
    <?php if($dismiss):?><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><?php endif?>
    <?php echo nl2br(h($message))?>
</div>