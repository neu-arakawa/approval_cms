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

<?php echo form_close(); ?>

</div> <!-- /container -->

<?php include_once VIEWPATH.'admin/common/footer.php'; ?>