$(document).ready(function(){
	var fileMaxByte = <?php echo size_to_byte( DEF('MAX_UPLOAD_FILE_SIZE') )?>; // ファイル1つあたりの最大ファイルサイズ
	var maxUploads = <?php echo DEF('MAX_FILE_UPLOADS')?>;	// 一度にアップロードできる最大ファイル数
	var debug = true;
	var currDir = null;
	var currHash = '';
	var $contentWrap = $('#pjax_container');
	var $dirTreeWrap = $('.dir-list');
	var dirTreeLinkSelector = '.dir-list a, .topic-path a';
	var dispSelector = '.act-display'; 	// ディレクトリリンク用セレクター
	var dirSelector = '.file-list .dir';
	var itemSelector = '.file-list li';
	var fnameSelector = '.file-list li .fname';
	var fnameCancelSelector = '.file-list li .fname .cancel';
	var fnameSubmitSelector = '.file-list li .fname .submit';
	var downloadSelector = '.act-download';
	var delSelector = '.act-delete';	// ファイル削除リンク用セレクター
	var selectSelector = '.act-select';	// ファイル選択リンク用セレクター
	var mkdirSelector = '.act-mkdir';	// ディレクトリ作成用セレクター
	var previewSelector = '.act-preview'; // プレビューリンクセレクター
	var mkdirNameSelector = '.mkdir-form [name="dir_name"]';	// ディレクトリ名入力フィールドセレクタ
	var searchNameSelector = '.search-form input';				// 検索フィールド名セレクタ
	var rmdirSelector = '.act-rmdir';	// ディレクトリ削除用セレクター
	var uploadFormSelector = '.upload-form';
	var uploadDirSelector = '.upload-form [name="dir"]';	// アップロードディレクトリフィールドセレクタ
	var $flashMessage = $('.flash-message');	// フラッシュメッセージ
	var $dndTarget = $('html');					// ドラッグ&ドロップ領域
	var $error = $('.error');					// エラーダイアログ
	var $fileLoading = $('.file-loading');		// ファイルのローディング
	var $preview = $('.preview');

	var fadeDuration = 100;						// フェードインアニメーション速度
	var processFlg = [];						// 処理フラグ

	var fileAPIEnable = window.File && window.FileReader && window.FileList && window.Blob;

	//
	// 初期化処理
	//
	function init(){

		// イベント設定
		$(window)
			.bind('hashchange', onHashChange)
			.bind('scroll resize', onScroll);
		$(document)
			.on('click', dispSelector, onDirInMainClick)
			.on('click', dirTreeLinkSelector, onDirInTreeClick)
			.on('click', dirSelector, onDirAreaInMainClick)
			.on('click', delSelector, onDelInMainClick)
			.on('click', rmdirSelector, onRmdirInMainClick)
			.on('click', mkdirSelector, onMkdirInMainClick)
			.on('keyup', searchNameSelector, onSearch)
			.on('click', fnameSelector, onFnameClick)
			.on('click', fnameCancelSelector, onFnameCancel)
			.on('click', fnameSubmitSelector, onFnameSubmit)
			.on('dblclick', itemSelector, onItemSelect)
			.on('click', selectSelector, onSelectClick)
			.on('click', downloadSelector, onDownloadClick)
			.on('click', previewSelector, onPreviewClick);
		$dndTarget
			.bind('dragover', onFileDragover)
			.bind('drop', onFileDrop);
		$error.bind('click', onErrorCloseClick);
		$preview.bind('click', onPreviewCloseClick);
		$preview.find('.close').bind('click', onPreviewCloseClick);

		display( decodeURIComponent(location.hash.substr(1)) )	// ファイル表示更新
			.then(function(){
				// 表示完了

				var dfd = jQuery.Deferred();

				// ディレクトリ一覧を取得する
				return ajax({
					data: {
						action: '<?php echo ACTION_DIRLIST?>',
						dir: currDir
					}
				})
				.done(function( data ){
					dfd.resolve( data );
				})
				.fail(function(){
					dfd.reject();
				});

				return dfd.promise();
			})
			.then(function( data ){
				// ディレクトリリストの配置
				$tree = $tree = createDirTree( data['result'] );
				$dirTreeWrap.empty().append( $tree );
			});
	}
	init();

	//
	// 表示の更新
	//
	function display( dir ){
		var dfd = new $.Deferred();

		// if( currDir!==null && currDir == dir ) return dfd.promise();
		if( processFlg['display'] ) return dfd.promise();
		
		if( !processFlg['display'] ) processFlg['display'] = 1;
		ctlLoading( true ); // ローディング表示

		_d('display:'+dir);

		var dfd = new $.Deferred();

		ajax({
			data: {
				action: '<?php echo ACTION_DISPLAY?>',
				dir: dir
			}
		})
		.then(function( data ){ // ページデータ取得成功
			// _d(data);
			
			if( data['errors'] ){ // エラーあり
				ctlError( true, data['errors'] );
				dfd.reject();
			}else{
				$contentWrap.empty().append( data['result'] ); // ファイルの表示更新
				currDir = dir;

				$(uploadDirSelector).val( currDir );
				currHash = '#'+ encodeURIComponent(currDir); // ハッシュを記録
				// _d(currHash);
				window.location.hash = encodeURIComponent(currDir); // ハッシュ変更(ディレクトリツリーアクティブ更新処理)
				activateCurrDir(); // ディレクトリツリーのアクティブ更新

				onScroll(); // ダミー画像をサムネイルに差し替えるためにスクロールイベント発火

				dfd.resolve();
			}
		})
		.fail(function( xhr, sta, err ){ // ページデータ取得失敗
			ctlError( true, 'ファイルの表示中にエラーが発生しました' );
			dfd.reject();
		})
		.always(function(){
			processFlg['display'] = 0;
			ctlLoading( false ); // ローディング非表示

			if( fileAPIEnable ){
				// $(uploadFormSelector).hide();
			}else{ // FileAPIが使用不可
				// POST形式のアップロードフォーム表示
				$(uploadFormSelector).show();
			}

		});

		return dfd.promise();
	}
	
	// 
	// ディレクトリのDOM木構造を作成する
	// (再帰)
	//
	function createDirTree( dirs, path ){
		var $elm = $('<ul></ul>');

		for( var dir in dirs ){
			var n, p;
			if( typeof path=='undefined' ) n = '';
			else n = dir;

			if( path ) p = path + '/' + n;
			else p = n;

			var $li = $('<li data-dir="'+p+'"><a href="#" draggable="false">'+dir+'</a></li>');
			if( p == currDir ) $li.addClass('active'); // 現在表示中のディレクトリをアクティブに

			$elm.append($li);
			if( dirs[dir] ){ // 下層にディレクトリあり
				$ret = createDirTree( dirs[dir], p );
				$li.append( $ret );
			}
		}
		return $elm;
	}

	//
	// 現在のディレクトリをツリー上でアクティブにする
	//
	function activateCurrDir(){
		$dirTreeWrap.find('li')
			.removeClass('active')
			.each(function(){
				if( $(this).attr('data-dir') == currDir ){
					$(this).addClass('active');
					return false;
				}
			});
	}

	//
	// ajax処理
	//
	function ajax( options ){
		var defaults = {
			url: './',
			type: 'POST',
			data: {},
			dataType: 'json',
			cache: false
		};
		var options = $.extend({}, defaults, options);

		var dfd = new $.Deferred;
		$.ajax( options )
			.done(function( data ){
				dfd.resolve( data );
			})
			.fail(function( xhr, sta, err ){
				dfd.reject( xhr, sta, err );
			});
		return dfd.promise();
	}

	//
	// ブラウザの戻る／進むによるハッシュチェンジイベント
	//
	function onHashChange(){
		_d('onHashChange');
		if( currHash != location.hash ){ // ハッシュが変更された
			_d('hash:'+location.hash + ' currHash:'+currHash);
			display( decodeURIComponent(location.hash.substr(1)) ) // ファイル表示更新
			.then(function(){ // 更新成功
				activateCurrDir(); // ディレクトリツリーのアクティブ更新
			});
		}
	}

	//
	// アイテムの選択
	//
	function onItemSelect( ev ){
		var $self = $(ev.currentTarget);
		if( $self.hasClass('dir') ) return false; // ディレクトリの場合は何もしない

		_d(window.parent);

		try{
            if( parent.tinymce !== undefined){
                parent.tinymce.activeEditor.windowManager.getParams().setUrl($self.attr('data-path') );
                parent.tinymce.activeEditor.windowManager.close();
				return false;
            }
			else if( window.opener && window.opener.CKEDITOR ){
				// CKEDITORが有効な場合

				var reParam = new RegExp('(?:[\?&]|&amp;)CKEditorFuncNum=([^&]+)', 'i') ;
				var match = window.location.search.match(reParam) ;
				var func = (match && match.length > 1) ? match[1] : '' ;

				window.opener.CKEDITOR.tools.callFunction(func, $self.attr('data-path') );
				window.close();
				return false;
			}
			else if( window.parent && window.parent.selectUploadFile ){
				// ArticleEditorが有効な場合
				window.parent.selectUploadFile( $self.attr('data-path') );
				return false;
			}
		}catch(e){
			// 例外が発生
			// クロスドメイン経由でArticleEditorから呼び出された場合など
			var d = JSON.stringify({path: $self.attr('data-path')});
			window.parent.postMessage(d,'*');
		}
	}
	//
	// アイテム選択ボタンクリック
	//
	function onSelectClick( ev ){
		_d('onSelectClick');
		var $self = $(ev.currentTarget);
		$self.closest( itemSelector ).trigger('dblclick');
		return false;
	}

	//
	// メイン画面上のディレクトリリンククリックイベント
	//
	function onDirInMainClick( ev ){
		_d('onDirInMainClick');
		var $self = $(ev.currentTarget);
		var $item = $self.closest( itemSelector );
		display( $item.attr('data-dir') );
		return false;
	}
	//
	// メイン画面上のディレクトリエリアクリックイベント
	//
	function onDirAreaInMainClick( ev ){
		_d('onDirAreaInMainClick');
		var $self = $(ev.currentTarget);
		$(this).find( dispSelector ).trigger( 'click' );
		return false;
	}

	//
	// ディレクトリツリーのリンククリックイベント
	//
	function onDirInTreeClick( ev ){
		_d('onDirInTreeClick');
		var $self = $(ev.currentTarget);
		var dir = $(this).closest('li').attr('data-dir');
		if( dir==currDir ) return false;
		display( dir );
		return false;
	}

	//
	// メイン画面上のファイル削除リンククリックイベント
	//
	function onDelInMainClick( ev ){
		if( processFlg['delete'] ) return false;
		
		_d('onDelInMainClick');

		var $self = $(ev.currentTarget);
		var $item = $self.closest( itemSelector );
		if( !confirm('本当に「'+$item.attr('data-fname')+'」を削除してもよろしいですか？') ){
			file_deleting = false;
			return false;
		}

		if( !processFlg['delete'] ) processFlg['delete'] = 1;
		ctlLoading( true ); // ローディング表示

		ajax({
			data: {
				action: '<?php echo ACTION_DELETE?>',
				dir: currDir,
				file_name: $item.attr( 'data-fname' )
			}
		})
		.fail(function( xhr, sta, err ){
			ctlError( true, 'ファイルの削除処理中にエラーが発生しました' );
		})
		.then(function( data ){ // ファイル削除完了
			if( data['errors'] ){
				ctlError( true, data['errors'] );
				return;
			}
			display( currDir );	// ファイル表示更新処理
		})
		.always(function(){
			processFlg['delete'] = 0;
			ctlLoading( false ); // ローディング非表示
		});

		return false;
	}

	//
	// メイン画面上のディレクトリ削除リンククリックイベント
	//
	function onRmdirInMainClick( ev ){
		if( processFlg['rmdir'] ) return false;

		var $self = $(ev.currentTarget);
		var $item = $self.closest( itemSelector );

		// ディレクトリ内にファイルが存在する場合は確認を取る
		var file_count = $item.attr('data-fcount');
		if( file_count > 0 ){
			if( !confirm( '「'+$item.attr('data-fname') +'」ディレクトリ内にはファイルおよびディレクトリが存在します。削除しますか？' ) ){
				return false;
			}
		}

		if( !processFlg['rmdir'] ) processFlg['rmdir'] = 1;

		ctlLoading( true ); // ローディング表示

		ajax({
			data: {
				action: '<?php echo ACTION_RMDIR?>',
				dir: currDir,
				dir_name: $item.attr( 'data-fname' )
			}
		})
		.then(function( data ){ // ディレクトリ削除成功
			display( currDir ); // ファイル表示更新

			// ディレクトリツリー更新
			$tree = createDirTree( data['result']['dir_list'] );
			$dirTreeWrap.empty().append( $tree );
		})
		.fail(function(){ // ディレクトリ削除失敗
			ctlError( true, 'ディレクトリの削除処理中にエラーが発生しました' );
		})
		.always(function(){
			processFlg['rmdir'] = 0;
			ctlLoading( false ); // ローディング非表示
		});

		return false;
	}

	//
	// ディレクトリ作成イベント
	//
	function onMkdirInMainClick( ev ){
		if( processFlg['mkdir'] ) return false;
		
		var $self = $(ev.currentTarget);

		$input = $(mkdirNameSelector);
		var dir = $input.val();
		if( !dir ){
			alert( 'ディレクトリ名を入力して下さい' );
			return false;
		}
		
		if( !processFlg['mkdir'] ) processFlg['mkdir'] = 1;
		ctlLoading( true ); // ローディング表示

		ajax({
			data: {
				action: '<?php echo ACTION_MKDIR?>',
				dir: currDir,
				dir_name: dir
			}
		})
		.then(function( data ){ // ディレクトリ作成成功
			if( data['errors'] ){
				ctlError( true, data['errors'] );
				return;
			}

			$input.val(''); // 入力ディレクトリ名をクリア
			display( currDir ); // ファイルの表示更新

			// ディレクトリツリー更新
			$tree = createDirTree( data['result']['dir_list'] );
			$dirTreeWrap.empty().append( $tree );
		})
		.fail(function( xhr, sta, err ){ // ディレクトリ作成失敗
			ctlError( true, 'ディレクトリの作成処理中にエラーが発生しました' );
		})
		.always(function(){
			processFlg['mkdir'] = 0;
			ctlLoading( false ); // ローディング非表示
		});

		return false;
	}

	// 
	// プレビュークリックイベント
	//
	function onPreviewClick( ev ){

		var $self = $(ev.currentTarget);
		var $item = $self.closest( itemSelector );

		$preview.fadeIn( fadeDuration );
		var $frame = $preview.find('.inner');
		$frame.removeClass('active');

		var $img = $('<img>').attr('src', $item.attr('data-path'));
		$img.bind('load',function(){
			// 画像ローディング完了

			var img_w = $(this).get(0).width;
			var img_h = $(this).get(0).height;

			var win_w = $(window).width();
			var win_h = $(window).height();

			var w, h;

			var rate = .85;
			w = win_w * rate;
			h = img_h * w / img_w;

			if( h > win_h * rate ){
				h = win_h * rate;
				w = img_w * h / img_h;
			}
			$img.css('width','100%');

			// _d('(win_w,win_h,img_w,img_h,w,h)=(' +win_w+','+win_h+','+img_w+','+img_h+','+w+','+h+')' );
			$frame
				.addClass('active')
				.css({'width': w+'px', 'height': h+'px'})
				.append($img);
		})
	}

	//
	// プレビュー閉じるクリック
	//
	function onPreviewCloseClick(){
		$preview.fadeOut( fadeDuration );
		var $frame = $preview.find('.inner');
		$frame
			.removeClass('active')
			.attr('style', '')
			.find('img').remove();
	}

	//
	// ファイル名クリックイベント
	//
	var $currFname;
	function onFnameClick( ev ){
		var $self = $(ev.currentTarget);	
		if( $currFname && $currFname.get(0)==$self.get(0) ){ // 編集中の項目がもう一度選択された
			return false;
		}else if( $currFname ){
			// すでに編集中のファイル名がある
			onFnameCancel();
		}

		$currFname = $self;
		
		var $item = $self.closest( itemSelector );
		var fname = $item.attr( 'data-fname' );
		$self.find('span').remove();
		$self.find('button.edit').hide();
		$self.append( '<div class="edit-wrap"><input type="text" value="'+fname+'" /><button class="cancel" title="キャンセル"><i class="fa fa-ban fa-lg" aria-hidden="true"></i></button><button class="submit" title="確定"><i class="fa fa-check-square fa-lg" aria-hidden="true"></i></button></div>' );
		return false;
	}

	//
	// ファイル名変更キャンセル
	//
	function onFnameCancel( ev ){
		if( !$currFname ) return false;

		var $item = $currFname.closest( itemSelector );
		var orig_name = $item.attr( 'data-fname' );

		$currFname.find('.edit-wrap').remove();
		var $fname = $('<span></span>');
		$fname.text( orig_name );
		$currFname.prepend($fname);
		$currFname.find('button.edit').show();

		$currFname = null;
		return false;
	}

	//
	// ファイル名変更決定
	//
	function onFnameSubmit( ev ){
		if( !$currFname ) return false;

		var $self = $(ev.currentTarget);
		var $item = $currFname.closest( itemSelector );
		var orig_name = $item.attr( 'data-fname' );
		var new_name = $currFname.find('input').val().trim();

		_d( 'onFnameSubmit: orig_name:'+orig_name + ' new_name:'+ new_name );

		if( !new_name ){
			alert('ファイル名を入力して下さい');
			return false;
		}

		if( !processFlg['rename'] ) processFlg['rename'] = 1;
		ctlLoading( true ); // ローディング表示

		ajax({

			data: {
				action: '<?php echo ACTION_RENAME?>',
				dir: currDir,
				curr_name: orig_name,
				new_name: new_name
			}
		})
		.fail(function( xhr, sta, err ){
			ctlError( true, 'ファイルのリネーム処理中にエラーが発生しました' );
		})
		.then(function( data ){ // ファイルリネーム完了
			if( data['errors'] ){
				ctlError( true, data['errors'] );
				return;
			}
			// _d(data);

			var $item = $currFname.closest( itemSelector );
			var item = data['result']['item'];

			if( $item.hasClass('dir') ){ // ディレクトリ
				$item
					.attr('data-dir', item['dir'])
					.attr('data-path', item['web_path'])
					.attr('data-fname', item['file_name'])
					.attr('data-fcount', item['file_count']);
			}else{ // ファイル
				$item
					.attr('data-path', item['web_path'])
					.attr('data-fname', item['file_name']);
			}

			// 表示をリネーム後の名前に変更
			$currFname.find('.edit-wrap').remove();
			var $fname = $('<span></span>');
			$fname.text( new_name );
			$currFname.prepend($fname);
			$currFname.find('button.edit').show();

			// ディレクトリツリーの更新
			$tree = createDirTree( data['result']['dir_list'] );
			$dirTreeWrap.empty().append( $tree );

			$currFname = null;
			// ディレクトリリンクを更新
			// _d($self.closest( itemSelector ));
			// _d($self.closest( itemSelector ));
			// $self.closest( itemSelector ).find( dispSelector ).attr( 'data-dir', new_name );
		})
		.always(function(){
			processFlg['rename'] = 0;
			ctlLoading( false ); // ローディング非表示
		});

		return false;
	}

	//
	// 検索処理
	//
	function onSearch( ev ){
		var $self = $(ev.currentTarget);

		var keyword = $self.val();
		$(itemSelector).each(function(){
			var fname = $(this).attr('data-fname');
			match = fname.indexOf(keyword)!=-1;
			if( match ){
				$(this).show();
			}else{
				$(this).hide();
			}
		});
	}

	//
	// ダウロードリンクのクリック
	//
	function onDownloadClick( ev ){
		var $self = $(ev.currentTarget);
		var $item = $self.closest( itemSelector );

		location.href = '?action=<?php echo ACTION_DOWNLOAD ?>&dir=' + currDir + '&file_name=' + $item.attr('data-fname');
		return false;
	}

	//
	// ファイルのドラッグオーバーイベント
	//
	function onFileDragover( ev ){
		// if( processFlg['upload'] ) return false;
		// _d('onFileDragover');

		ev.stopPropagation();
		ev.preventDefault();
		ev.originalEvent.dataTransfer.dropEffect = 'copy';
	}

	//
	// ファイルのドロップイベント
	//
	var file_loading = false;
	var file_count = 0;
	var loadedFile = [];
	function onFileDrop( ev ){
		if( processFlg['upload'] ) return false;

		_d('onFileDrop');
		
		loadedFile = [];

		ev.stopPropagation();
		ev.preventDefault();

		var files = ev.originalEvent.dataTransfer.files;
		file_count = files.length;

		if( file_count == 0 ) return false;

		if( file_count > maxUploads ){
			// ファイル数が上限を超過
			ctlError( true, '一度にアップロードできる最大数は'+maxUploads+'個までです' );
			return false;
		}

		if( !processFlg['upload'] ) processFlg['upload'] = 1; // 処理フラグをONに
		ctlLoading( true, 0 );

		var fd = new FormData();
		fd.append( 'dir', currDir );
		fd.append( 'action', '<?php echo ACTION_UPLOAD ?>' );
		var errors = [];
		for( var i=0; i<files.length; i++ ){
			var f = files[i];
			_d(f);
			if( f['size'] > fileMaxByte ){
				// ファイルサイズの上限を超過
				errors.push( f['name'] + ': ファイルサイズの上限(<?php echo DEF('MAX_UPLOAD_FILE_SIZE') ?>)を超過しています' );
			}
			fd.append( 'upload[]', f );
		}

		if( errors.length ){ // エラーあり
			ctlLoading( false, 0 );
			ctlError( true , errors ); // エラー表示
			processFlg['upload'] = 0; // 処理フラグOFF
		}else{
			// ファイル送信処理
			onFileLoaded( fd );
		}
	}

	//
	// ドラッグ&ドロップ読み込み完了イベント
	//
	function onFileLoaded( fd ){
		_d('onFileLoaded');

		// ファイルをPOST送信
		ajax({
			data: fd,
			contentType: false,
			processData: false,
			xhr: function(){ // プログレスバー表示のための処理
				var xhr = $.ajaxSettings.xhr();
				if( xhr.upload ){
					xhr.upload.addEventListener('progress', function( ev ){
						if( ev.lengthComputable ){
							percent = Math.ceil( ev.loaded / ev.total * 100 );
							ctlLoading( null, percent );
						}
						_d(percent);
					}, false);
				}
				return xhr;
			}
		})
		.then(function( data ){ // ファイルの送信成功
			// _d(data);
			if( data['errors'] ){ // エラーあり
				ctlError( true, data['errors'] );
			}
			display( currDir ); // ページ再表示
		})
		.fail(function(req, sta, err){ // ファイル送信失敗
			console.log(err);
			ctlError( true, 'ファイルの送信時にエラーが発生しました' );
		})
		.always(function(){
			processFlg['upload'] = 0; // 処理フラグOFF
			ctlLoading( false, 0 ); // ローディング非表示
		});
	}

	//
	// エラーの閉じるクリックイベント
	//
	function onErrorCloseClick(){
		ctlError( false );
		return false;
	}

	function onScroll(){
		var top = $(this).scrollTop();
		var height = $(window).height();

		$(itemSelector+' img[data-src]').each(function(){
			var img_top = $(this).offset().top;
			// _d( $(this).attr('data-src') + ' (top,img_top,top+height)=('+ top+','+img_top+','+(top+height) );
			if( top < img_top && img_top < top+height  ){
				// 表示領域にある

				// 実際のサムネイル画像を表示する
				$(this)
					.attr('src', $(this).attr('data-src'))
					.removeAttr('data-src');
			}
		});
	}

	//
	// ローディングの表示処理
	//
	function ctlLoading( open, progress ){
		var count = $fileLoading.attr('data-count');
		if( typeof count == 'undefined' ) count = 0;

		if( open === true ){ // 表示する
			count++;
			if( count==1 && !$fileLoading.is(':visible') ){
				// 他の処理からの表示処理中でない、かつ非表示の状態
				$fileLoading.fadeIn( fadeDuration );
			}
		}else if( open === false ){ // 閉じる
			count--;
			if( count==0 && $fileLoading.is(':visible') ){
				// 他の処理からの閉じる処理中でない、かつ表示の状態
				$fileLoading.fadeOut( fadeDuration );
			}
		}

		if( typeof progress != 'undefined' ){
			$fileLoading.find('.message').hide();
			$fileLoading.find('.progress').show();

			$fileLoading.find('.progress .bar').css('width', progress+'%');
		}else{
			$fileLoading.find('.message').show();
			$fileLoading.find('.progress').hide();
		}
		
		// 処理カウントを設定
		$fileLoading.attr('data-count', count);
	}


	//
	// エラー表示処理
	//
	function ctlError( open, errors ){

		if( typeof errors == 'string' ) errors = [errors];

		if( open ){
			if( !$error.is(':visible') ){
				$error.fadeIn( fadeDuration );
			}
			
			if( errors ){
				var tag = '';
				for( var k in errors ){
					var e = errors[k];
					tag += '<li>'+e+'</li>';
				}

				$error.find('ul')
						.empty()
						.append(tag);
			}
		}else{
			if( $error.is(':visible') ){
				$error.fadeOut( fadeDuration );
			}
		}
	}
	

	function _d( msg ){
		if( !debug ) return false;
		if( typeof console === "undefined" || typeof console.log === "undefined" ){
			console = {};
		}else{
			console.log( msg );
		}
	}
	
});
