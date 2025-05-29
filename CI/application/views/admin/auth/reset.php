<?php include_once VIEWPATH.'admin/common/header.php'; ?>

<div class="container">

<h1>
  <a class="navbar-brand" href="/" target="_blank">
    <img src="<?php echo base_url('/admin/img/logo.png');?>" height="70" class="d-inline-block align-middle" alt="<?php echo SITE_NAME?>" />
  </a>
</h1>

<?php echo form_open(); ?>
  <h2>パスワードのリセット</h2>

  <?php $this->flash->render(null, false);?>

  <div class="mb-2">
  <small class="form-text text-muted">新しいパスワードを入力して[パスワードの再設定]ボタンを押してください</small>
  </div>

  <?php echo admin_validation_errors()?>

  <div class="form-row">
    <div class="form-group col-12">
      <label>新しいパスワード</label>
      <?php echo my_form_password(AUTH_PASSWORD_FIELD, '', ['class'=>'form-control','label'=>'新しいパスワード']);?>
    </div>
    <div class="form-group col-12">
      <label>パスワード（再入力）</label>
      <?php echo my_form_password(AUTH_PASSWORD_FIELD.'_confirm', '', ['class'=>'form-control','label'=>'パスワード（再入力）']);?>
    </div>
  </div>

  <?php echo form_button([
    'type'=>'submit',
    'name'=>'submit',
    'class'=>'btn btn-lg btn-primary btn-block',
    'content'=>'<i class="fas fa-sign-in-alt"></i> パスワードの再設定'
  ]);?>

<?php echo form_close(); ?>

</div> <!-- /container -->

<?php include_once VIEWPATH.'admin/common/footer.php'; ?>