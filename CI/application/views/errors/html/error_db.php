<?php if (ADMIN_MODE || config_item('enable_profiler')): // 管理画面?>
  <?php include('error_common.php');?>
<?php else:?>
  <?php include(WWW_ROOT.'/404.php');?>
<?php endif?>
