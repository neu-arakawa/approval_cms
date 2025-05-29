<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <title><?php if (!empty($title)) echo h($title); else if (!empty($entity_name)) echo h($entity_name);?> | <?php echo SITE_NAME ?> 管理画面</title>
  <link rel="shortcut icon" type="image/x-icon" href="/common/img/admin_favicon.ico" />

  <!-- dependency -->
  <script src="<?php echo base_url('/admin/js/jquery-3.1.1.min.js');?>"></script>
  <script src="<?php echo base_url('/admin/js/jquery-ui/jquery-ui.min.js');?>"></script>
  <link href="<?php echo base_url('/admin/js/jquery-ui/jquery-ui.min.css');?>" rel="stylesheet">
  <script src="<?php echo base_url('/admin/js/popper.min.js');?>"></script>

  <!-- tinymce -->
  <script src="<?php echo base_url('/admin/js/tinymce/tinymce.min.js');?>"></script>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="<?php echo base_url('/admin/bootstrap4/css/bootstrap.min.css')?>">
  <script src="<?php echo base_url('/admin/bootstrap4/js/bootstrap.min.js');?>"></script>

  <!-- Bootstrap Plugins -->
  <!-- datepicker -->
  <script src="<?php echo base_url('/admin/bootstrap4/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js');?>"></script>
  <script src="<?php echo base_url('/admin/bootstrap4/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.ja.min.js');?>"></script>
  <link href="<?php echo base_url('/admin/bootstrap4/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css');?>" rel="stylesheet" />

  <!-- datetimepicker -->
  <script src="<?php echo base_url('/admin/js/datetimepicker/jquery.datetimepicker.full.min.js');?>"></script>
  <link href="<?php echo base_url('/admin/js/datetimepicker/jquery.datetimepicker.css');?>" rel="stylesheet" />

  <!-- switch -->
  <link href="<?php echo base_url('/admin/css/lib/pretty-checkbox.min.css');?>" rel="stylesheet" />

  <!-- fontawesome -->
  <link rel="stylesheet" href="<?php echo base_url('/admin/font-awesome-5/css/fontawesome-all.min.css');?>">

  <!-- For Article CSS Styles -->
  <link rel="stylesheet" href="<?php echo base_url('/admin/js/attach-upload-vue/css/style.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('/admin/js/article-editor-vue/css/style.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('/admin/js/inline-editor-vue/css/style.css');?>">

  <link rel="stylesheet" href="<?php echo base_url('/admin/css/common.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('/admin/css/style.css');?>">
  <link rel="stylesheet" href="<?php echo base_url('/admin/css/print.css');?>" media="print">

  <!-- For Article JS -->
  <script src="<?php echo base_url('/admin/js/attach-upload-vue/attach-upload-vue.min.js');?>"></script>
  <script src="<?php echo base_url('/admin/js/article-editor-vue/article-editor-vue.min.js');?>"></script>
  <script src="<?php echo base_url('/admin/js/inline-editor-vue/inline-editor-vue.min.js');?>"></script>

  <!-- preview for AttachUpload -->
  <link href="<?php echo base_url('/admin/js/attach-upload-vue/css/preview.css');?>" rel="stylesheet">

  <!-- preview for ArticleEditor -->
  <link href="<?php echo base_url('/admin/js/article-editor-vue/css/preview.css');?>" rel="stylesheet">
  <link href="<?php echo base_url('/admin/js/article-editor-vue/css/ve.css');?>" rel="stylesheet">

  <!-- preview for InlineEditor -->
  <link href="<?php echo base_url('/admin/js/inline-editor-vue/css/ve.css');?>" rel="stylesheet">

  <!-- library for modal -->
  <script src="<?php echo base_url('/admin/js/modal.js');?>"></script>
  <!-- librayr for layered select -->
  <script src="<?php echo base_url('/admin/js/layered_select.js');?>"></script>

  <script>
  var isConfirm = <?php echo !empty($confirm_flag) ? 1 : 0 ?>;
  $(document).ready(function(){

    // 必須マーク設定
    $('[data-required="1"]').closest('tr').addClass('required');
    $('tr.required').each(function(){
      $(this).find('th').append('<?php echo admin_required_badge() ?>');
    });
    // チェックボックス、ラジオボタンにバリデーションエラークラスが付いていたら
    // 対応するラベルにも付加する
    $('input[type="checkbox"].is-invalid, input[type="radio"].is-invalid').each(function(){
      var $p = $(this).closest('label');
      var $n = $(this).next('label');
      var $elm;
      if ($p.length) $elm = $p;
      else if ($n.length) $elm = $n;
      if ($elm) $elm.addClass('is-invalid');
    });

    // 日付ピッカー
    $('.datepicker').datepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      clearBtn: true,
      language: 'ja'
    });

    // 日時ピッカー
    $.datetimepicker.setLocale('ja');
    $('.datetimepicker').datetimepicker({
      format: 'Y-m-d H:i'
    });

    // タイムピッカー
    $('.timepicker').datetimepicker({
      datepicker: false,
      format: 'H:i'
    });

    $(".alert").alert();

    $('[data-toggle="tooltip"]').tooltip();

    new AttachUpload('.attach-upload',{
      maxFileSize :'<?php echo ini_get('upload_max_filesize') ?>B',
      uploaderPath: '<?php echo UPLOADER_DIR;?>'
    });
    // ★注★ ArticleEditorおよびInlineEditorでinlineVeモードを使用する場合、
    // tinymceと共存は不可
    new ArticleEditor('.article-editor',{
      maxFileSize :'<?php echo ini_get('upload_max_filesize') ?>B',
      uploaderPath: '<?php echo UPLOADER_DIR;?>',
      veStyleFormats : <?php echo json_encode( $this->config->item('block_editor_style_formats') ) ?>,
      inlineVe: true
    });
    new InlineEditor('.inline-editor',{
      uploaderPath: '<?php echo UPLOADER_DIR;?>',
      veStyleFormats : <?php echo json_encode( $this->config->item('block_editor_style_formats') ) ?>
    });

    // ★注★ ArticleEditorおよびInlineEditorでinlineVeモードを使用する場合、
    // tinymceと共存は不可
    //tinymce.init({
    //    selector: ".wysiwyg",
    //    language: "ja", // 言語 = 日本語
    //    theme: 'modern',
    //    skin: 'lightgray',
    //    menubar: false,
    //});

    // エラー項目のある行にクラスを付ける
    $('.is-invalid').closest('tr').addClass('error');


    // 検索用アコーディオン処理
    var $detail_search = $('.search-box .detail-search');
    var $acd_btn = $($detail_search.find('> a'));
    var $target = $($acd_btn.attr('href'));
    $acd_btn.bind('click',function(){
      var is_visible = $target.is(':visible');
      if (is_visible) { // 非表示にする
        $acd_btn.html('<i class="fas fa-plus-circle"></i> 検索条件をより細かく設定する');
      } else {
        $acd_btn.html('<i class="fas fa-times-circle"></i> 細かい検索条件を閉じる');
      }
    });

    // 詳細条件エリア内の入力フィールドに条件が指定されているかチェック
    var input_any = false;
    $target.find('input, select, textarea').each(function(){
      var name = $(this).attr('name');
      var tag = $(this).get(0).tagName.toUpperCase();
      var type = $(this).attr('type') ? $(this).attr('type').toUpperCase() : null;

      var val = '';
      if (tag == 'INPUT' && type == 'RADIO') {
        val = $target.find('[name="'+name+'"]').filter(':checked').val();
      } else if (tag == 'INPUT' && type == 'CHECKBOX') {
        val = $(this).prop('checked') ? $(this).val() : '';
      } else {
        val = $(this).val();
      }

      if (val) {
        input_any = true;
        return false;
        // console.log([name,tag,type, val]);
      }
    });
    if (input_any) { // 入力があれば詳細検索を表示する
      $acd_btn.trigger('click');
    }

    // ナビメニューの現在地表示設定
    var curr_url = '<?php
      if ($method=="edit" && empty($model_id)) { // 新規作成
        // 新規作成時、アクションはadd→editに転送されるので、
        // addアクションとして判定を行う
        echo admin_base_url($controller."/add");
      } else {
        echo uri_string();
      }
    ?>';
    $('#sidenavi .nav-item a').each(function(){
      var href = $(this).attr('href');
      var reg = new RegExp(curr_url+'\/?$');
      if (href.match(reg)) { // URLがマッチ
        $(this).closest('.nav-item').addClass('active');
      }
    });

    // モーダル関連処理
    new ModalSelect();
    new ModalCtl({'base_url':'<?php echo base_url('/') ?>'});

    // サイドナビ処理
    var $naviToggler = $('.sidenavi-toggler');
    var $sidenavi = $('#sidenavi');
    $naviToggler.bind('click', function(){
      if ($sidenavi.hasClass('open')) {
        $(this).removeClass('open');
        $sidenavi.removeClass('open');
      } else {
        $(this).addClass('open');
        $sidenavi.addClass('open');
      }
      window.scrollTo(0,0);
      return false;
    });

    // サイトナビ処理
    var $naviToggler = $('.mymenu-toggler');
    var $mymenu = $('.mymenu');
    $naviToggler.bind('click', function(){
      if ($mymenu.hasClass('open')) {
        $(this).removeClass('open');
        $mymenu.removeClass('open');
      } else {
        $(this).addClass('open');
        $mymenu.addClass('open');
      }
      window.scrollTo(0,0);
      return false;
    });

    // レスポンシブテーブルのスクロールバー有無の監視
    var last_resive_tm = 0;
    var $rsp = $('.table-responsive');
    $rsp.wrap('<div class="table-responsive-wrap"></div>');
    $rsp.each(function(){
      if ($(this).hasClass('reverse-scroll')) {
        $(this).closest('.table-responsive-wrap').addClass('reverse-scroll');
      }
    });
    $(window).bind('resize orientationchange', function(){
      $rsp.each(function(){
        var $tbl = $(this).find('table');
        // alert($tbl.width()+' '+$rsp.width());
        var $wrap = $(this).closest('.table-responsive-wrap');
        if ($tbl.width() > $rsp.width()) {
          // スクロールバーが表示されていると判定
          $wrap.addClass('scrollable');
        } else {
          $wrap.removeClass('scrollable');
        }
      });
    });
    $(window).trigger('resize');

  });
  </script>
  <style>
  .article-editor {
    z-index: unset;
  }
  </style>
</head>

<?php if(!empty($is_login)): // ログイン?>
  <body class="signin">
<?php elseif (!empty($is_modal)): // モーダル?>
  <body class="admin admin-modal">
    <main>
<?php else: // その他のページ?>
  <body class="admin<?php echo !empty($confirm_flag) ? ' confirm' : ' input';?>">

    <div style="position:relative; height:auto; min-height:100%; ">
    <header>
      <nav class="navbar navbar-expand-md align-middle">
        <button class="sidenavi-toggler" type="button">
          <span class="sidenavi-toggler-icon"></span>
        </button>

        <h1>
          <a class="navbar-brand" href="<?php echo admin_base_url() ?>"><img src="<?php echo base_url('/admin/img/logo.svg')?>" height="33" class="d-inline-block align-middle" alt="<?php echo SITE_NAME?>"/></a>
        </h1>
        <div class="environment <?php echo ENVIRONMENT ?>">
          <?php echo ENVIRONMENT_NAME ?>
        </div>

        <button class="mymenu-toggler" type="button">
          <span class="mymenu-toggler-icon"></span>
        </button>

        <div class="mymenu">
          <div class="admin-name">
            <?php
                $greeting_message = '';
                if (date("H") >= 6 and date("H") <= 11) :
                    $greeting_message = 'おはようございます';
                elseif (date("H") >= 12 and date("H") <= 17) :
                    $greeting_message = 'こんにちは';
                else :
                    $greeting_message = 'こんばんは';
                endif;
            ?>
            <span class="user-name"><?php echo $greeting_message  ?> <?php echo h($this->session->admin->name) ?> さん </span>
            <span class="user-edit inline-block">( <a href="<?php echo admin_base_url('/user/my_edit'); ?>"><i class="fas fa-pencil-alt"></i> ユーザ編集</a> )</span>
          </div>

          <ul>
            <li>
              <a class="btn btn-dark btn-site btn-sm" href="<?php echo base_url() ?>" target="_blank"><i class="fas fa-external-link-alt"></i> サイトを見る</a>
            </li>
            <li>
              <a class="btn btn-dark btn-logout btn-sm" href="<?php echo admin_base_url('/logout'); ?>"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
            </li>
          </ul>

        </div>
      </nav>
    </header>

    <nav id="sidenavi">
      <?php include_once VIEWPATH.'admin/common/sidebar.php'; ?>
    </nav>
    <main>
      <?php $this->flash->render(); // フラッシュメッセージ表示?>
<?php endif?>
