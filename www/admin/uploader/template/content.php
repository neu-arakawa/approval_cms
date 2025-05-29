<?php
//
// メインエリアのテンプレート（ファイルリスト表示エリア）
//

$tmpl_dir = substr( __DIR__, strlen(WWW_ROOT) );

?>

<ul class="topic-path">
	<li data-dir=""><a href="#"><i class="fa fa-home" aria-hidden="true"></i></a></li>
	<?php
	if( !empty($curr_dir) ):
	$paths = explode('/', $curr_dir);
	$path_arr = array();
	foreach( $paths as $path ):
		$path_arr[] = $path;
		?>
		<li data-dir="<?php echo implode('/', $path_arr) ?>"><a href="#"><?php echo $path?></a></li>
	<?php endforeach;?>
	<?php endif?>
</ul>

<div class="header">

	<div class="search-form">
		<input type="text" name="keyword" placeholder="検索キーワードを入力" />
		<button class="act-search"><i class="fa fa-search" aria-hidden="true"></i> 検索</button>
	</div>

	<?php if( DEF('MKDIR_ENABLED')):?>
	<div class="mkdir-form">
		<input type="text" name="dir_name" placeholder="ディレクトリ名を入力" />
		<button class="act-mkdir"><i class="fa fa-plus" aria-hidden="true"></i> 作成</button>
	</div>
	<?php endif?>

	<div class="upload-form">
		<form method="post" enctype="multipart/form-data">
			<input class="upload" type="file" name="upload" />
			<input type="hidden" name="action" value="<?php echo ACTION_UPLOAD?>" />
			<input type="hidden" name="dir" />
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo size_to_byte(DEF('MAX_POST_SIZE')) ?>" />
			<button type="submit">アップロード</button>
		</form>
	</div>
	<div class="max-upload-size">
        最大アップロードサイズ: <?php echo DEF('MAX_UPLOAD_FILE_SIZE') ?>
	</div>
</div>

<?php if( !empty( $disp_list ) ):?>

<ul class="file-list">
	<?php foreach( $disp_list as $item ):?>
		<?php if( $item['is_dir'] ): // ディレクトリ?>
			<li data-dir="<?php echo $item['dir'] ?>"
				data-path="<?php echo $item['web_path']?>"
				data-fname="<?php echo $item['file_name'] ?>"
				data-fcount="<?php echo $item['file_count']?>"
				class="dir">
			<div class="thumb">
				<img src="<?php echo $tmpl_dir?>/img/dir.svg" draggable="false" />
			</div>
			<div class="meta">
				<div class="fname"><a href="#" class="act-display"><span><?php echo $item['file_name'] ?></span></a>
					<?php if( $item['is_renameable'] ): // リネーム可?>
						<button title="リネーム" class="edit"><i class="fa fa-pencil fa-lg" aria-hidden="true"></i></button>
					<?php endif?>
				</div>
				<?php /*
				<div class="fperm"><span>権　限</span>
					<label class="<?php echo !$item['is_readable'] ? 'disabled' : ''?>">読</label>
					<label class="<?php echo !$item['is_writable'] ? 'disabled' : ''?>">書</label>
					<label class="<?php echo !$item['is_deletable'] ? 'disabled' : ''?>">消</label>
					<label class="<?php echo !$item['is_renameable'] ? 'disabled' : ''?>">名</label>
				</div>
				*/ ?>
				<div class="control">
					<?php if( $item['is_deletable'] ):?>
						<a href="#" class="act-rmdir"><i class="fa fa-trash" aria-hidden="true"> 削除</a></i>
					<?php else:?>
						<span class="label warn">削除不可</span>
					<?php endif?>
				</div>
			</div>
		<?php else: // ファイル?>
			<li data-path="<?php echo $item['web_path']?>"
				data-fname="<?php echo $item['file_name'] ?>"
				class="">
			<div class="thumb">
				<?php if( $item['is_image'] ):?><button class="act-preview" title="プレビュー"><i class="fa fa-search-plus fa-lg" aria-hidden="true"></i></button><?php endif?>
				<img class="<?php echo $item['is_image'] ? 'cover' : '' ?>" draggable="false" data-src="<?php echo $item['thumb_web_path']; ?>" width="100" />
			</div>
			<div class="meta">
				<div class="fname"><span><?php echo $item['file_name'] ?></span>
					<?php if( $item['is_renameable'] ): // リネーム可?>
						<button title="リネーム" class="edit"><i class="fa fa-pencil fa-lg" aria-hidden="true"></i></button>
					<?php endif?>
				</div>
				<div class="ftype"><span>種　類</span><?php echo $item['ext'] ?></div>
				<div class="fsize"><span>サイズ</span><?php echo $item['size'] ?></div>
				<div class="ftime"><span>変更日</span><?php echo date('Y-m-d H:i:s', $item['modified'])?></div>
				<?php /*
				<div class="fperm"><span>権　限</span>
					<label class="<?php echo !$item['is_readable'] ? 'disabled' : ''?>">読</label>
					<label class="<?php echo !$item['is_writable'] ? 'disabled' : ''?>">書</label>
					<label class="<?php echo !$item['is_deletable'] ? 'disabled' : ''?>">消</label>
					<label class="<?php echo !$item['is_renameable'] ? 'disabled' : ''?>">名</label>
				</div>
				*/ ?>
				<div class="control">
					<a href="#" class="act-select" data-fname="<?php echo $item['file_name'] ?>"><i class="fa fa-arrow-right" aria-hidden="true"></i> 選択</a></i>&nbsp;
					<?php if( $item['is_readable'] ):?>
						<a href="#" class="act-download"><i class="fa fa-download" aria-hidden="true"> ダウンロード</a></i>
					<?php endif?>&nbsp;
					<?php if( $item['is_deletable'] ):?>
						<a href="#" class="act-delete"><i class="fa fa-trash" aria-hidden="true"> 削除</a></i>
					<?php else:?>
						<span class="label warn">削除不可</span>
					<?php endif?>

				</div>
			</div>
		<?php endif?>
	</li><?php endforeach?>
</ul>
<?php else:?>
	<div class="no-file">
		<div class="inner">このフォルダにはファイルがありません</div>
	</div>
<?php endif?>
