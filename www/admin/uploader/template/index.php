<?php
$tmpl_dir = substr( __DIR__, strlen(WWW_ROOT) );
$flash_msg = flash_message();
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>uploader</title>

    <link rel="stylesheet" href="<?php echo $tmpl_dir?>/font-awesome/css/font-awesome.min.css">

    <link href="<?php echo $tmpl_dir?>/css/common.css" rel="stylesheet" type="text/css" media="all">
    <link href="<?php echo $tmpl_dir?>/css/style.css" rel="stylesheet" type="text/css" media="all">
	<script src="<?php echo $tmpl_dir?>/js/jquery-3.2.1.min.js"></script>
	<script><?php
	require_once( __DIR__.'/js/main.js' );
	?></script>
  </head>

<body class="<?php echo DEF('UPLOAD_MODE')==='cms' ? 'cms' : '' ?>">
	<div class="overlay file-loading">
		<div class="message">データ処理中</div>
		<div class="progress">
			<div class="bar-bg">
				<div class="bar"></div>
			</div>
			データ処理中
		</div>
	</div>

	<div class="overlay error">

		<div class="inner">
			<button class="close"><i class="fa fa-times fa-lg" aria-hidden="true"></i></button>
			<div class="error">
				<h3><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> エラーがあります</h3>
				<ul>
				</ul>
			</div>
			<!--<div class="progress">
				<div class="bar"></div>
			</div>-->
		</div>
	</div>

	<div class="overlay preview">
		<div class="inner">
			<button class="close"><i class="fa fa-times fa-lg" aria-hidden="true"></i></button>
			<!-- <img src="/upload/neko_yoko.jpg"> -->
		</div>
	</div>

	<div class="sidebar">
		<div class="dir-list">
		</div>
	</div>

	<div class="main">
		<?php if( !empty($flash_msg) ):?>
		<ul class="flash-message">
			<?php foreach( $flash_msg as $msg ):?>
				<li><?php echo $msg ?></li>
			<?php endforeach?>
		</ul>
		<?php endif?>

		<div id="pjax_container">
		</div>

	</div>



</body>
</html>
