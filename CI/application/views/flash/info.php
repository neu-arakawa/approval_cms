<?php /* 通常通知のフラッシュメッセージ */?>
<div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <?php echo nl2br(h($message))?>
</div>