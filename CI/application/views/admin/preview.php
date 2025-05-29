<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>プレビュー | 管理画面</title>

  <!-- dependency -->
  <script src="<?php echo base_url('/admin/js/jquery-3.1.1.min.js')?>"></script>
  <script src="<?php echo base_url('/admin/js/jquery-ui/jquery-ui.min.js')?>"></script>
  <link href="<?php echo base_url('/admin/js/jquery-ui/jquery-ui.min.css')?>" rel="stylesheet">
  <script src="<?php echo base_url('/admin/js/popper.min.js')?>"></script>

  <link rel="stylesheet" href="<?php echo base_url('/admin/font-awesome-5/css/fontawesome-all.min.css')?>">

  <!-- Bootstrap -->
  <link rel="stylesheet" href="<?php echo base_url('/admin/bootstrap4/css/bootstrap.min.css')?>">
  <script src="<?php echo base_url('/admin/bootstrap4/js/bootstrap.min.js')?>"></script>

  <link rel="stylesheet" href="<?php echo base_url('/admin/css/common.css')?>">
  <style type="text/css">
  header nav {
    display: flex!important;
  }
  h1 {
    padding: 0 0 0 1rem!important;
    flex-shrink: 0;
  }
  .alert {
    white-space: normal;
  }
  body{
    position: relative;
    height: 100%;
    overflow-y: hidden;
  }
  .iframe-body{
    height: 100%;
    padding: 0;
    margin: 0;
    /*overflow: hidden;*/
  }
  .overlay{
    background-color: rgba(0,0,0,.6);
    position: fixed;
    width: 100%;
    height: 100%;
  }
  iframe{
    position: relative;
    width: 100%;
    height: 100%;
    border: none;
  }
  </style>
</head>

<body class="admin-preview">
  <header>
    <nav class="navbar navbar-expand-md align-middle">
      <h1>
        <a class="navbar-brand" href="<?php echo admin_base_url() ?>"><img src="<?php echo base_url('/admin/img/logo.png')?>" height="45" class="d-inline-block align-middle" /> <?php echo SITE_NAME?></a>
      </h1>
      <div class="alert alert-danger m-0 p-1 pl-3 pr-3" role="alert">
        <i class="icon fa fa-exclamation-triangle"></i> この画面はプレビューです。サイト上では公開されていません。
      </div>
    </nav>
  </header>

  <?php /* 入力が不完全な場合の警告ダイアログ */ ?>
  <div class="modal warning-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">注意</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>この記事は入力が不完全なため、正しいレイアウトで表示されない可能性があります。<br>入力を続行される場合は一覧に戻り「編集」ボタンから編集を行ってください。</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
        </div>
      </div>
    </div>
  </div>

  <div class="iframe-body">
    <iframe id="preview" src="<?php echo h($url)?>">
    </iframe>
  </div>

  <script>
  // iframe内のページリンクのターゲットを_parentに書き換え
  // (リンククリックでiframeが外れるように)
  $(document).ready(function(){
    var $preview = $('#preview');
    var validated = <?php echo !empty($_GET['validated']) ? 'true' : 'false'?>;

    function waitPreview(){
      var timer = setInterval(function(){
        var len = $preview.contents().find('body').children().length;
        if(len){
          clearInterval(timer);
          $preview.contents().find('a').each(function(){
            if($(this).attr('target')=='_blank') return;
            $(this).attr('target','_parent');
          });
        }
      },500);
    }
    waitPreview();

    if (!validated) {
      $('.warning-modal').modal('show');
    }
  });
  </script>
</body>

</html>