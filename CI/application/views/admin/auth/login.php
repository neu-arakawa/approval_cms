<?php include_once VIEWPATH.'admin/common/header.php'; ?>

<div class="container">

<h1>
  <a class="navbar-brand" href="/" target="_blank">
    <img src="<?php echo base_url('/admin/img/logo.svg');?>" height="50" class="d-inline-block align-middle" alt="<?php echo SITE_NAME?>" />
  </a>
</h1>

<?php echo form_open(); ?>
  <h2>管理用のログイン名とパスワードを入力してください</h2>

  <?php $this->flash->render();?>

  <div class="form-row">
    <div class="form-group col-12">
      <label>ログイン名</label>
      <?php echo my_form_input(AUTH_USER_FIELD, '', ['class'=>'form-control','label'=>'ログイン名']);?>
    </div>
    <div class="form-group col-12">
      <label>パスワード</label>
      <?php echo my_form_password(AUTH_PASSWORD_FIELD, '', ['class'=>'form-control','label'=>'パスワード']);?>
    </div>
  </div>

  <?php echo form_button([
    'type'=>'submit',
    'name'=>'submit',
    'class'=>'btn btn-lg btn-primary btn-block',
    'content'=>'<i class="fas fa-sign-in-alt"></i> ログイン'
  ]);?>

  <?php if (PASSWORD_RESET_ENABLED):?>
  <div class="mt-2">
    <a href="<?php echo admin_base_url('/request_reset') ?>"><i class="fas fa-key"></i> パスワードを忘れた場合</a>
  </div>
  <?php endif?>

<?php echo form_close(); ?>

</div> <!-- /container -->

<?php include_once VIEWPATH.'admin/common/footer.php'; ?>
