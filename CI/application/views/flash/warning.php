<?php /* 警告時のフラッシュメッセージ */?>
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <?php echo nl2br(h($message))?>
</div>