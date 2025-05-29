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
  <small class="form-text text-muted">入力したメールアドレス宛に、パスワードのリセットのためのリンクが送信されます。<?php echo PASSWORD_RESET_EXPIRES?>分以内にリンクをクリックして手続きを行ってください。</small>
  </div>

  <div class="form-row">
    <div class="form-group col-12">
      <label>メールアドレス</label>
      <?php echo my_form_input(AUTH_EMAIL_FIELD, '', ['class'=>'form-control','label'=>'メールアドレス']);?>
    </div>
  </div>

  <?php echo form_button([
    'type'=>'submit',
    'name'=>'submit',
    'class'=>'btn btn-lg btn-primary btn-block',
    'content'=>'<i class="fas fa-sign-in-alt"></i> メールを送信'
  ]);?>

  <div class="mt-2">
    <a href="<?php echo admin_base_url('/login') ?>"><i class="fas fa-arrow-circle-left"></i> ログインに戻る</a>
  </div>

<?php echo form_close(); ?>

</div> <!-- /container -->

<?php include_once VIEWPATH.'admin/common/footer.php'; ?>