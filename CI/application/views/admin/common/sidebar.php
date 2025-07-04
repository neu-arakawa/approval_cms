<?php 
// 権限設定
$user = $this->my_session->admin;
?>
<div class="navbar align-middle">
  <ul class="nav nav-pills">

    <li class="nav-item">
      <h3><a href="<?php echo admin_base_url('/') ?>" class="nav-link"><i class="fas fa-tachometer-alt"></i> ダッシュボード</a></h3>
    </li>

    <li class="nav-item">
      <h3><span><?php echo admin_view_icon('admin/news') ?> 新着情報管理</span></h3>
      <ul class="nav">
        <li class="nav-item"><a href="<?php echo admin_base_url('/news/add') ?>" class="nav-link">新規登録</a></li>
        <li class="nav-item"><a href="<?php echo admin_base_url('/news/') ?>" class="nav-link">一覧</a></li>
      </ul>
    </li>

    <?php if ($user->flg_admin): // 管理者のみ?>
    <li class="nav-item">
      <h3><span><?php echo admin_view_icon('admin/user') ?> ユーザ管理</span></h3>
      <ul class="nav">
        <li class="nav-item"><a href="<?php echo admin_base_url('/user/add') ?>" class="nav-link">新規登録</a></li>
        <li class="nav-item"><a href="<?php echo admin_base_url('/user/') ?>" class="nav-link">一覧</a></li>
      </ul>
    </li>

    <li class="nav-item">
      <h3><a href="<?php echo admin_base_url('/log/') ?>" class="nav-link"><?php echo admin_view_icon('admin/log') ?> 操作履歴</a></h3>
    </li>
    <?php endif?>

    <?php if (ENVIRONMENT==='local' || ENVIRONMENT==='neulocal'):?>
    <li class="nav-item">
      <h3><a href="<?php echo admin_base_url('/styles/') ?>" class="nav-link"><i class="fas fa-gavel"></i> スタイル調整用</a></h3>
    </li>
    <?php endif?>

  </ul>
</div>
