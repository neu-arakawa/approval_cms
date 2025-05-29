/*
 * ArticleEditor
 * ver. 1.18
 *
 * require IE >= 11
 * depend on Vue / jQuery (autoload)
 *
 */

/*
 出力されるHTMLソースは下記の構造
<div class="ve">
  <h3>大見出し<br>改行可</h3>
  <h4>中見出し<br>改行可</h4>
  <h5>小見出し<br>改行可</h5>
  <p>本文<br>改行可</p>
  <div class="image-wrap">
    <p class="img img-left"><img src="/upload/xxx.jpg" title="xxx.jpg"></p>
    <p class="text">左寄せ画像の回り込みテキスト</p>
  </div>
  <div class="image-wrap">
    <p class="img img-right"><img src="/upload/xxx.jpg" title="xxx.jpg"></p>
    <p class="text">右寄せ画像の回り込みテキスト</p>
  </div>
  <div class="image-wrap">
    <p class="img img-center"><img src="/upload/xxx.jpg" title="xxx.jpg"></p>
    <p class="text">中央寄せ画像の回り込みテキスト</p>
  </div>
  <div class="link-wrap"><a href="/" target="_blank">リンクテキスト</a></div>
  <ul>
    <li>リスト1</li>
    <li>リスト2</li>
  </ul>
  <table>
    <thead>
      <tr>
        <th scope="col">横に並ぶ見出し</th>
        <th scope="col">横に並ぶ見出し</th>
        <th scope="col">横に並ぶ見出し</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>セル</td>
        <td>セル</td>
        <td>セル</td>
      </tr>
    </tbody>
  </table>
  <table>
    <tbody>
      <tr>
        <th scope="row">縦にならぶ見出し</td>
        <td>セル</td>
        <td>セル</td>
      </tr>
      <tr>
        <th scope="row">縦にならぶ見出し</td>
        <td>セル</td>
        <td>セル</td>
      </tr>
    </tbody>
  </table>
</div>
*/
var ArticleEditor = (function(){

  var DEBUG = true;
  var SCRIPT_DIR_PATH = getScriptPath();

  var FILE_API_ENABLED = window.File && window.FileReader && window.FileList && window.Blob;
  var BROWSER = getBrowser();
  var SUPPORTED = ( BROWSER=='edge' || BROWSER=='chrome' || BROWSER=='safari' || BROWSER=='firefox' ||
          (BROWSER=='ie' && getIEVersion()>=10 ) ) && FILE_API_ENABLED;

  // CSSを動的にロード
  loadCSS(SCRIPT_DIR_PATH + 'css/style.css');
  loadCSS(SCRIPT_DIR_PATH + 'css/ve.css');
  loadCSS(SCRIPT_DIR_PATH + 'css/vue-transition.css');
  loadCSS(SCRIPT_DIR_PATH + 'lib/font-awesome/font-awesome.min.css');

  var ArticleEditor = function(element, options){

    var $elm = $(element);
    var $form = $elm.closest('form');
    if( !$form.length ){
      alert('[ArticleEditor] 指定の要素がフォーム要素に含まれないため、正常に動作しません');
      return null;
    }

    var editor = this;

    // デフォルトのオプション設定　//////////////////////////////////////////
    var defaultOptions = {
      'maxFileSize':'2MB',          // アップロード可能な最大ファイルサイズ（サーバサイドの設定値よりも小さい値を入れること）
      'types': ['big-heading', 'mid-heading', 'small-heading', 'text', 'image', 'image-kmu', 'vs-image', 'link', 'link-kmu', 'list', 'list-kmu', 'list-link-kmu','table', 'html', 'youtube'], // サポートする種類
      'typeNames': {
          'big-heading':'大見出し', 
          'mid-heading':'中見出し', 
          'small-heading':'小見出し', 
          'text':'テキスト', 
          'image':'画像', 
          'image-kmu':'画像', 
          'link':'リンク', 
          'link-kmu':'リンク', 
          'list':'リスト', 
          'list-kmu':'リスト',
          'list-link-kmu':'リンク', 
          'table':'表組み', 
          'html': 'HTML', 
          'youtube': 'YouTube', 
          'vs-image': '画像(横2列)'
      },
      'allowCssClass': false,           // CSSクラスの入力を許可するか
      'allowImageAlt': false,           // （画像項目のみ）Altの入力を許可するか
      'bigHeadingTag': 'h3',            // 大見出しのタグ
      'midHeadingTag': 'h4',            // 中見出しのタグ
      'smallHeadingTag': 'h5',          // 小見出しのタグ
      'textTag': 'p',                   // 本文のタグ
      'imageTag': 'div',                // 画像のタグ
      'imageTagClass': 'image-wrap',    // 画像のクラス
      'linkTag': 'div',                 // リンクのタグ
      'linkTagClass': 'link-wrap',      // リンクのクラス
      'listTag': 'ul',                  // リストのタグ
      'tableTag': 'table',              // テーブルのタグ
      'tableDefaultColNum': 2,          // テーブルのデフォルト列数
      'tableMaxRow': 50,                // テーブルの最大行数
      'tableMaxCol': 10,                // テーブルの最大列数
      'htmlTag': 'div',                 // HTMLのタグ
      'htmlTagClass': 'html-wrap',      // HTMLのクラス
      'wholeWrapTag': 'div',            // 全体を囲むタグ
      'wholeWrapTagClass': 've',        // 全体を囲むタグのクラス
      'imageTypes': ['jpg','jpeg','jpe','gif','png'], // 許可する画像の種類(img src=""に設定して問題ないものを指定すること)
      'fileTypes': ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','mp4','jpg','jpeg','jpe','gif','png'], // 許可するファイルの種類（指定しない場合はnull）
      'imgPlaceholderText': '回り込みテキストを入力します。回り込みテキストを入力します。回り込みテキストを入力します。回り込みテキストを入力します。回り込みテキストを入力します。回り込みテキストを入力します。',
      'maxTextAreaHeight': 400,         // テキストエリアの最大高さ
      'uploaderDomain': null,           // Uploaderプラグインのドメイン（このプラグインと別ドメインで稼働している場合）
      'uploaderPath': '/uploader',      // Uploaderプラグインのパス(Web)
      'uploaderDir': '/',               // Uploaderプラグインのアップロードディレクトリ名(/始まり)
      'inlineVe': true,                 // インラインでのビジュアルエディタ(wysiwygエディタ)の有効／無効
      'allowHtmlInText': false,         // テキスト入力フィールドで手動HTML入力を許可するか(inlineVeが有効な場合、テキスト、および画像ではVisualEditorによってテキストを編集するため、HTMLの入力は不可。inlineVeが無効な場合は見出し、テキスト、画像でHTMLの入力が可能)
      // 'veInlineClasses': {'bold':'太字', 'highlight':'ハイライト', 'link':'リンク' }, // キーにはクラス名を、値にはメニューに表示されるタイトルを設定。linkの場合は例外的にクラスではなく、リンクの挿入に対応する場合に設定
      'veStyleFormats': [],
      'onLoad': null,            // 要素のセットアップが完了して使用可能になったときのコールバック
      'onUpdate': null,                 // 要素の変更検知コールバック(リアルタイムプレビュー実装等)
      'updateInterval': 0               // onUpdateコールバックを呼び出す間隔(msec)(最初の変更を検知してから設定msecの間は一回のコールバックにまとめられる。完全リアルタイムにしたい場合は0を設定する。)
    };

    // 設定値のセットアップ ////////////////////////////////////////////////
    this.options = cloneHash( defaultOptions );
    var droppingItem = null; // ドラッグ＆ドロップ操作中の要素
    var currentItem = null; // 挿入位置判定のためのセパレータ
    var currentTable = null;  // 現在対象のテーブル

    for( var k in options ){
      this.options[k] = options[k];
    }

    // 要素ごとに設定されたdata-*属性からオプションの上書きを行う
    for( var k in defaultOptions ){
      var tag_prop = 'data-'+ kebabCase(k);
      var tag_val = $elm.attr(tag_prop);
      if(tag_val){
        tag_val = tag_val.replace(/\'/g,'"'); // シングルクォーテーションをダブルに変換
        try{
          tag_val = JSON.parse(tag_val);
        }catch(e){
        }finally{
          // console.log(tag_val);
          this.options[k] = tag_val; // オプション上書き
        }
      }
    }

    // アップローダのパスの末尾のスラッシュを補う
    if( !this.options.uploaderPath.match(/\/$/) ) this.options.uploaderPath = this.options.uploaderPath+'/';
    // アップロードディレクトリ名の先頭と末尾のスラッシュを除去する
    if( this.options.uploaderDir.match(/^(\/*)(.*?)(\/*)$/) ) this.options.uploaderDir = RegExp.$2;
    // アップロードドメインの末尾のスラッシュを除去する
    if( this.options.uploaderDomain && this.options.uploaderDomain.match(/^(.*?)(\/*)$/) ) this.options.uploaderDomain = RegExp.$1;

    // console.log(this.options);
    // コンポーネントの定義 ////////////////////////////////////////////////
    // 要素共通処理用ミックスイン ///////////////////////////////////////////////////
var itemMixin = {
  data: function(){
    return {
      dragging: false
    };
  },
  computed: {
    isCssClassEnabled: function () {
      return editor.options.allowCssClass;
    }
  },
  methods: {
    onLabelDragstart: function(ev){
      // _d('label dragstart');

      ev.dataTransfer.setData('text', 'element');
      ev.dataTransfer.effectAllowed = 'move';

      this.dragging = true;
      this.$emit('item-dragstart', this.item);
    },
    onLabelDragend: function(ev){
      this.dragging = false;
      this.$emit('item-dragend', this.item);
    },
    onLabelDrop: function(){
      this.$emit('item-drop', this.item);
    },
    onSeparatorClick: function(_, ev){
      this.$emit('separator-click', this.item, ev);
    },
     // 要素の削除
    onItemDelete: function(ev){
      // イベント発火
      this.$emit('item-delete', this.item);
    }
  }
};
    var separatorComponent = {
  template: '<div class="separator" v-bind:class="{dragover:dragover}" v-on:click.prevent.stop="onClick" v-on:dragover.prevent.stop="onDragover" v-on:dragleave="onDragleave" v-on:drop.prevent.stop="onDrop"><button tabindex="-1"><span>挿入</span></button></div>',
  data: function(){
    return {
      dragover: false
    };
  },
  methods: {
    onClick: function(ev){
      this.$emit('separator-click', null, ev);
    },
    onDragover: function(ev){
      if( !droppingItem ) return false; // 要素のドラッグ&ドロップ処理でなければスキップ
      this.dragover = true;

      ev.dataTransfer.dropEffect = 'move';
    },
    onDragleave: function(ev){
      if( !droppingItem ) return false; // 要素のドラッグ&ドロップ処理でなければスキップ
      this.dragover = false;
    },
    onDrop: function(ev){
      if( !droppingItem ) return false; // 要素のドラッグ&ドロップ処理でなければスキップ
      this.dragover = false;
      this.$emit('label-drop-on-separator');
    }
  }
};
    // メニューコンポーネント ///////////////////////////////////////////////////
var itemMenuTemplate = '<div class="item-menu" v-bind:class="[data.direction,{show: data.show}]"><ul>';
editor.options.types.forEach(function(type){
  var label = editor.options.typeNames[type];
  itemMenuTemplate += '<li data-type="'+type+'" v-on:click="onMenuClick">'+label+'</li>';
});
itemMenuTemplate += '</ul></div>';

var itemMenuComponent = {
  template: itemMenuTemplate,
  props: ['data'],
  computed: {
  },
  mounted: function(){
    // サイズ取得
    this.data.size.width = this.$el.clientWidth;
    this.data.size.height = this.$el.clientHeight;
  },
  methods: { 
    onMenuClick: function(ev){
      var type = $(ev.target).attr('data-type');
      this.$emit('menu-click', type);
    }
  }
};
    // プレーンテキスト入力コンポーネント ///////////////////////////////////////////////////
// (≠テキストエリア)
// 画像の回り込み用のcontenteditableなdiv
var plainTextInputComponent = {
  // blurイベントを設定するのは、VE経由でcontenteditableを操作する場合、inputイベントが発火しないため
  template: '<div contenteditable="true" v-bind:class="{placeholded: placeholded}" v-on:input="onTextChange" v-on:focus="onFocus" v-on:blur="onBlur" v-on:paste.prevent="onTextPaste" ref="inputElement"></div>',
  props: ['text', 'isPlaceholderEnabled'],
  data: function(){
    return {
      placeholded: false
    };
  },
  created: function(){

  },
  mounted: function(){
    // データをcontentEditableの領域に反映させる
    var $dom = $(this.$refs.inputElement);

    if( this.text ) $dom.html('<p>'+this.text+'</p>');
    else $dom.html('<br>'); // 入力が全くないとキャレットが消失するのを回避

    // プレースホルダの入力ため、フォーカス解除イベントを強制発火
    this.onBlur();
  },
  computed: {
    
  },
  methods: {
    // contentEditable領域の変更イベント
    onTextChange: function(ev){
      // テキスト入力のイベント発火
      this.$emit('input', $(this.$refs.inputElement).html());
    },

    onFocus: function(ev){
      this.$emit('focus');
      var $elm = $(this.$refs.inputElement);
      if( this.isPlaceholderEnabled && $elm.text() === editor.options.imgPlaceholderText ){
        // プレースホルダが入力されている場合はクリアする
        this.placeholded = false;
        $elm.html('<br>');
      }
    },

    onBlur: function(ev){
      this.onTextChange(ev);
      this.$emit('blur');

      var $elm = $(this.$refs.inputElement);
      if( this.isPlaceholderEnabled && !$elm.text() ){
        // 入力がない場合はプレースホルダを入力する
        this.placeholded = true;
        setTimeout(function(){
          $elm.html(editor.options.imgPlaceholderText);
        },0);
        
      }
    },

    // ペーストイベント
    onTextPaste: function(ev){
      // プレーンテキストでペースト処理を行う
      var text;
      if( window.clipboardData ) text = window.clipboardData.getData('text');
      else text = ev.clipboardData.getData('text/plain');

      if( document.selection ){
        var range = document.selection.createRange();
        range.text = text;
      }else{
        var selection = window.getSelection();
        var range = selection.getRangeAt(0);
        var node = document.createTextNode(text);
        range.deleteContents();
        range.insertNode(node);
        selection.removeAllRanges();
      }

    }
  }
};
    // ビジュアルエディタコンポーネント ///////////////////////////////////////////////////
var VEComponent = {
  // blurイベントを設定するのは、VE経由でcontenteditableを操作する場合、inputイベントが発火しないため
  template: '<div class="ve"\
              contenteditable="false"\
              v-bind:class="{placeholded: placeholded}"\
              v-on:input="onTextChange"\
              v-on:blur="onBlur"\
              v-on:focus="onFocus"\
              ref="inputElement"></div>',
  props: ['text', 'isPlaceholderEnabled'],
  data: function(){
    return {
      placeholded: false,
      veMenuSelector: '.mce-tinymce', // TinyMCEのメニューのDOMセレクタ
    };
  },
  created: function(){
  },
  mounted: function(){
    // データをcontentEditableの領域に反映させる
    var $dom = $(this.$refs.inputElement);

    if( this.text ) $dom.html(this.text);
    else $dom.html('<br>'); // 入力が全くないとキャレットが消失するのを回避

    if( editor.options.inlineVe ){ // VEモードON
      this.loadTinyMCE(); // TinyMCEのロード
    }

    // プレースホルダの入力ため、フォーカス解除イベントを呼び出し
    this.onBlur();
  },
  computed: {
    
  },
  methods: {
    init: function(){
      var self = this;

      var toolbar = 'undo redo | link unlink | removeformat';
      var styles  = editor.options.veStyleFormats;
      if ( styles.length ){
          // toolbar = 'undo redo | styleselect link unlink | removeformat';
          toolbar = 'styleselect link unlink | removeformat';
      }
      //styles =  [
      //  { title: 'ハイライト', items: [
      //    { title: '赤マーカー', inline:'span',classes: 'highlight__red' },
      //    { title: '緑マーカー', inline:'span',classes: 'highlight__green' }
      //  ]},
      //  { title: '文字色', items: [
      //    { title: '赤', inline:'span',classes: 'red' },
      //    { title: '緑', inline:'span',classes: 'green' }
      //  ]},
      //  { title: '白抜き', items: [
      //    { title: '赤ベタ', inline:'span',classes: 'beta__red' },
      //    { title: '緑ベタ', inline:'span',classes: 'beta__green' }
      //  ]},
      //  {title: '太字', inline:'span', classes:'bold'}
      //];

      var maxCount = 20;
      var count = 0;
      var timer = setInterval(function(){
        if( typeof tinymce !== 'undefined' ){
          // ライブラリの読み込み完了
          // tinymceのスクリプトがロードされた直後は
          // tinymce.init処理を実行すると
          // 失敗することがあるため、間隔を開けて何度かリトライする
          var file_manager_url = editor.options.uploaderPath;
          if( editor.options.uploaderDir ) file_manager_url += '#' + editor.options.uploaderDir;

          tinymce.init({
            target: self.$el,
            theme: 'inlite',
            skin: 'custom',
            language: 'ja',
            menubar: false,
            statusbar: false,
            plugins: 'link paste',
            insert_toolbar: '',
            selection_toolbar: toolbar,
            inline: true,
            paste_as_text: true,
            paste_data_images: false,
            forced_root_block: false,
            keep_styles: false,
            hidden_input: false,
            content_css: [],
            branding: false,
            elementpath: false,
            style_formats: styles,
            convert_urls: false,
            relative_urls: true,
            init_instance_callback: function(editor){
              clearInterval(timer);
              self.$emit('ve-init'); // 初期化完了を親コンポーネントに伝える
            },
            file_browser_callback: function(field_name, url, type, win) {
                tinymce.activeEditor.windowManager.open({
                    file: file_manager_url, 
                    title: 'ファイルのアップロード・選択',
		            width : window.innerWidth * 0.85,
		            height : window.innerHeight * 0.75,
                    resizable: 'yes'
                }, {
                    setUrl: function (url) {
                        win.document.getElementById(field_name).value = url;
                    }
                });
                return false;
            }
          });
        }
        count++;

        if( count >= maxCount ){
          // リトライ回数が上限に達した
          clearInterval(timer);
          return;
        }

      },500);

    },

    // contentEditable領域の変更イベント
    onTextChange: function(ev){
      // テキスト入力のイベント発火
      var html = $(this.$refs.inputElement).html();
      this.$emit('input', html);
    },

    // テキスト入力エリアのフォーカスイベント
    onFocus: function(ev){
      this.$emit('focus');

      var $elm = $(this.$refs.inputElement);
      if( this.isPlaceholderEnabled && $elm.text() === editor.options.imgPlaceholderText ){
        // プレースホルダが入力されている場合はクリアする
        this.placeholded = false;
        $elm.html('<br>');
      }
    },

    // テキスト入力エリアのフォーカス解除イベント
    onBlur: function(ev){
      this.onTextChange(ev);
      this.$emit('blur');

      var $elm = $(this.$refs.inputElement);
      if( this.isPlaceholderEnabled && !$elm.text() ){
        // 入力がない場合はプレースホルダを入力する
        this.placeholded = true;
        setTimeout(function(){
          $elm.html(editor.options.imgPlaceholderText);
        },0);
        
      }
    },

    // TinyMCEのロード
    loadTinyMCE: function(){
      var self = this;

      if( typeof tinymce==='undefined' ){
        // TinyMCEがロードがされていない場合のみロードする
        loadScript(SCRIPT_DIR_PATH + 'lib/tinymce/tinymce.min.js', function(){
          self.init(self);
        });
      }else{
        self.init(self);
      }

    }
  }
};

    // ビジュアルエディタコンポーネント ///////////////////////////////////////////////////
var wysiwygComponent = {
  // blurイベントを設定するのは、VE経由でcontenteditableを操作する場合、inputイベントが発火しないため
  template: '<div class="ve" ref="editor"\
              contenteditable="false"\
              v-bind:class="{placeholded: placeholded}"\
              ref="inputElement"></div>',
  props: ['text', 'isPlaceholderEnabled'],
  data: function(){
    return {
      placeholded: false,
      veMenuSelector: '.mce-tinymce', // TinyMCEのメニューのDOMセレクタ
    };
  },
  created: function(){
  },
  mounted: function(){
    // データをcontentEditableの領域に反映させる
    var $dom = $(this.$refs.inputElement);

    if( this.text ) $dom.html(this.text);
    else $dom.html('<br>'); // 入力が全くないとキャレットが消失するのを回避

      // this.loadTinyMCE(); // TinyMCEのロード

    this.loadCkeditor();

    // プレースホルダの入力ため、フォーカス解除イベントを呼び出し
    // this.onBlur();
  },
  methods: {
    init: function(){
      var self = this;

      var toolbar;
      var styles = [];
      for( var cls in editor.options.veInlineClasses ){
        var title = editor.options.veInlineClasses[cls];
        if( cls==='link' ) toolbar = 'styleselect link unlink | removeformat | code';
        else styles.push({title: title, inline:'span', classes:cls});
      }
      if( !toolbar ) toolbar = 'styleselect | removeformat';

      toolbar = 'styleselect link unlink | removeformat | image table template code';
      var maxCount = 20;
      var count = 0;
      var timer = setInterval(function(){
        if( typeof tinymce !== 'undefined' ){
          // ライブラリの読み込み完了
          // tinymceのスクリプトがロードされた直後は
          // tinymce.init処理を実行すると
          // 失敗することがあるため、間隔を開けて何度かリトライする

          tinymce.init({
            target: self.$el,
            theme: 'modern',
            skin: 'custom',
            height: 500,
            language: 'ja',
            menubar: false,
            resize: 'both',
            statusbar: false,
            plugins: 'link paste table code image template',
            insert_toolbar: '',
            // selection_toolbar: toolbar,
            toolbar: toolbar,
            // inline: true,
            paste_as_text: true,
            paste_data_images: false,
            forced_root_block: false,
            keep_styles: false,
            hidden_input: false,
            content_css: [],
            branding: false,
            elementpath: false,
            style_formats: styles,
            convert_urls: false,
            relative_urls: true,
            body_class : "ve",
            content_css: '../dist/css/preview.css, ../dist/css/ve.css',
            templates: [
              {title: '左寄せ画像の回り込みテキスト',   content: '<div class="image-wrap"><p class="img img-left"><img src="https://placehold.jp/1500x800.png" /></p><p class="text">左寄せ画像の回り込みテキスト</p></div>'},
              {title: '右寄せ画像の回り込みテキスト',   content: '<div class="image-wrap"><p class="img img-right"><img src="https://placehold.jp/1500x800.png" /></p><p class="text">右寄せ画像の回り込みテキスト</p></div>'},
              {title: '中央寄せ画像の回り込みテキスト', content: '<div class="image-wrap"><p class="img img-center"><img src="https://placehold.jp/1500x800.png" /></p><p class="text">中央寄せ画像の回り込みテキスト</p></div>'},
              {title: 'リンク',   content: '<div class="link-wrap"><a href="https://www.google.com/">google</a></div>'},
              {title: 'リスト',   content: '<ul><li>あああ</li><li>いいい</li><li>ううう</li></ul>'},
              {title: 'テーブル（1列目タイトル）', content: 
                    `<table>
                    <tbody>
                    <tr>
                    <th scope="row">プロジェクト参加者</th>
                    <td>xxxxxxxxxxx</td>
                    </tr>
                    <tr>
                    <th scope="row">キャラクタープロフィール</th>
                    <td>あーなちゃん：<a href="#" target="_blank" rel="noopener">xxxxxxxxx</a><br /></td>
                    </tr>
                    <tr>
                    <th scope="row">本件に関するお問い合わせ</th>
                    <td>マスクプロジェクト委員会<br />メール：<a href="mailto:info@xxxxx.com">info@xxxxx.com</a><br />電話番号：xxxxxxx</td>
                    </tr>
                    </tbody>
                    </table>`
              },
              {title: 'テーブル（1行目タイトル）', content: 
                    `<table>
                    <tbody>
                    <tr>
                    <th scope="row">プロジェクト参加者</th>
                    <th>キャラクタープロフィール</th>
                    <th>本件に関するお問い合わせ</th>
                    </tr>
                    <tr>
                    <td scope="row">xxxxxxxxxxxxxx</td>
                    <td>あーなちゃん：<a href="#" target="_blank" rel="noopener">xxxxxxxxx</a><br /></td>
                    <td>マスクプロジェクト委員会<br />メール：<a href="mailto:info@xxxxx.com">info@xxxxx.com</a><br />電話番号：xxxxxxx</td>
                    </tr>
                    </tbody>
                    </table>`
              }
            ],
            file_browser_callback : function(field_name, url, type, win){ 
                tinymce.activeEditor.windowManager.open({
                    file: editor.options.uploaderPath, 
                    title: 'ファイルのアップロード・選択',
                    width: 1200,  
                    height: 1200,
                    resizable: 'yes'
                }, {
                    oninsert: function (url) {
                        win.document.getElementById(field_name).value = url;
                    }
                });
    
                return false;
            }, 
            init_instance_callback: function(editor){
              clearInterval(timer);
              self.$emit('ve-init'); // 初期化完了を親コンポーネントに伝える
            },
            setup: function (editor) {
              editor.on('change', function () {
                self.$emit('input', editor.getContent());
              });
            }
          });
        }
        count++;

        if( count >= maxCount ){
          // リトライ回数が上限に達した
          clearInterval(timer);
          return;
        }

      },500);

    },

    // TinyMCEのロード
    loadTinyMCE: function(){
      var self = this;

      if( typeof tinymce==='undefined' ){
        // TinyMCEがロードがされていない場合のみロードする
        loadScript(SCRIPT_DIR_PATH + 'lib/tinymce/tinymce.min.js', function(){
          self.init(self);
        });
      }else{
        self.init(self);
      }

    },


    initCkeditor: function(){
      var self = this;
      var editor = CKEDITOR.replace( self.$el,{
          // extraPlugins : 'video,youtube',
          toolbar : [
            ['Styles'],
      		['Image','Video'],
      		['Link', 'Unlink'],
      		['TextColor', 'BGColor'],
      		['Bold','Italic','Underline','Strike'],
            ['Table'],
            ['RemoveFormat','Source', 'Maximize'],
          ],
          bodyClass: 've',
          allowedContent : true,
          height : 300,
          filebrowserBrowseUrl: '/uploader/',
          contentsCss: ['../dist/css/preview.css', '../dist/css/ve.css' ],
          stylesSet : [
            { name: 'ハイライト', element: 'span', attributes: {'class': 'highlight'} },
            // { name: 'Red Background', element: 'p', styles: { 'background-color': 'red' } }
          ] 
      });
      editor.on('change',function(){
        self.$emit('input', editor.getData());
      });

    },

    // Ckeditorのロード
    loadCkeditor: function(){
      var self = this;

      if( typeof CKEDITOR==='undefined' ){
        loadScript([
            'https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.21.0/ckeditor.js'
        ], function(){
            CKEDITOR.disableAutoInline = true;
            self.initCkeditor(self);
        });
      }else{
        self.initCkeditor(self);
      }
    }

  }
};

    // テキストコンポーネント ///////////////////////////////////////////////////
var textComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item text" v-bind:class="[item.type, {dragging:dragging}]">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">{{labelName}}</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <textarea v-model="item.text" v-on:input="onInput" ref="inputElement" />\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    separator: separatorComponent
  },
  mixins: [itemMixin],
  mounted: function(){
    // テキストエリアの高さ調整のためにinputイベントを強制発火
    // jqueryではなぜかinputイベントが発火しないため
    // 生のdomで実装する
    var elm = this.$refs.inputElement;
    if( document.createEvent ){
      // IE以外
      var evt = document.createEvent('HTMLEvents');
      evt.initEvent('input', true, true ); // event type, bubbling, cancelable
      elm.dispatchEvent(evt);
    }else{
      // IE
      var evt = document.createEventObject();
      elm.fireEvent('oninput', evt);
    }
  },
  computed: {
    labelName: function(){
      return editor.options.typeNames[this.item.type];
    }
  },
  methods: {
    onInput: function(ev){
      // 入力値に応じてリサイズする

      var def_style = window.getComputedStyle(ev.target);
      var line_height = def_style.lineHeight.split('px')[0];
      var padding = def_style.paddingTop.split('px')[0] + def_style.paddingBottom.split('px')[0];

      if( ev.target.scrollHeight > ev.target.offsetHeight ){
        ev.target.style.height = ev.target.scrollHeight+3 + 'px';
      }else{
          
          var height;
          while( true ){
            height = ev.target.style.height.split('px')[0];

            ev.target.style.height = height - line_height +'px';
            if( ev.target.scrollHeight > ev.target.offsetHeight ){
              ev.target.style.height = ev.target.scrollHeight+3 + 'px';
              // _d(ev.target.scrollHeight + padding_top + padding_bottom);
              break;
            }
            break;
          }
      }
      // _d(ev.target.scrollHeight);
      // _d(ev.target.offsetHeight);

    }
  }
};

    // ビジュアルエディタ対応テキストコンポーネント ///////////////////////////////////////////////////
var VETextComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item image ve-text" v-bind:class="{\'ve-unload\': !item.veInited, dragging:dragging}">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">テキスト</label>\
        <div class="align">\
          <input type="radio" value="left" v-model="item.align" v-bind:id="randomID[0]" /><label v-bind:for="randomID[0]"></label>\
          <input type="radio" value="center" v-model="item.align" v-bind:id="randomID[1]" /><label v-bind:for="randomID[1]"></label>\
          <input type="radio" value="right" v-model="item.align" v-bind:id="randomID[2]" /><label v-bind:for="randomID[2]"></label>\
        </div>\
      </div>\
      <div class="col2" v-bind:class="item.align">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <visualeditor\
          v-bind:text=item.text\
          v-bind:isPlaceholderEnabled=false\
          v-on:input="onTextChange"\
          v-on:ve-init="onVEInit" />\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  <div>',
  props: ['item'],
  data: function(){
    return {
      randomID: [],
    }
  },
  created: function(){
  },
  components: {
    visualeditor: VEComponent,
    separator: separatorComponent
  },
  mixins: [itemMixin],
  mounted: function(){
    for(var i=0; i<3; i++) this.randomID.push('image-align-'+getRandomString(6,'numeric'));
  },
  computed: {
    labelName: function(){
      return editor.options.typeNames[this.item.type];
    }
  },
  methods: {
    // VE上でのテキスト入力イベント
    onTextChange: function(ev){
      this.item.text = ev;
    },

    // VEの初期化完了
    onVEInit: function(){
      this.item.veInited = true;
    }
  }
};

    // 画像コンポーネント ///////////////////////////////////////////////////
var imageComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item image" v-bind:class="{\'ve-unload\': !item.veInited, nofile: !item.image, dragover:dragover, dragging:dragging, focus:focus}" v-on:dragover.prevent.stop="onDragover" v-on:dragleave="onDragleave" v-on:drop.prevent.stop="onDrop">\
      <div class="loading" v-if="isLoadingFile"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">画像</label>\
        <div class="align">\
          <input type="radio" value="left" v-model="item.align" v-bind:id="randomID[0]" /><label v-bind:for="randomID[0]"></label>\
          <input type="radio" value="center" v-model="item.align" v-bind:id="randomID[1]" /><label v-bind:for="randomID[1]"></label>\
          <input type="radio" value="right" v-model="item.align" v-bind:id="randomID[2]" /><label v-bind:for="randomID[2]"></label>\
        </div>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="dnd" v-if="isDndSupported" v-on:dblclick="selectFile">\
          <div class="msg">画像をドラッグ＆ドロップ</div>\
        </div>\
        <div class="content">\
          <div class="img" v-bind:class="item.align">\
            <img v-bind:src="item.image" v-on:dblclick="selectFile" draggable="false" />\
          </div>\
          <component\
            v-bind:is="inputComponent"\
            v-bind:isPlaceholderEnabled=true\
            class="text"\
            v-bind:text=item.text\
            v-on:input="onTextChange"\
            v-on:ve-init="onVEInit"\
            v-on:focus="onFocus"\
            v-on:blur="onBlur" />\
        </div>\
        <div class="alt-wrap" v-if="isAltEnabled">\
          <label :for="item.id+\'_alt\'">ALT</label>\
          <input type="text" v-model="item.altName" :id="item.id+\'_alt\'" />\
        </div>\
        <button class="remove" tabindex="-1" v-on:click.prevent="onFileDelete">画像をクリア</button>\
        <button class="uploader" tabindex="-1" v-on:click.prevent="selectFile">アップロード済みの画像から選択</button>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  data: function(){
    return {
      randomID: [],
      dragover: false,
      isDndSupported: editor.options.uploaderDomain ? false : true,
      isLoadingFile: false,
      uploadProgress: 0,
      inputComponent: editor.options.inlineVe ? 'visualeditor' : 'plaintexteditor',
      focus: false
    }
  },
  components: {
    visualeditor: VEComponent,
    plaintexteditor: plainTextInputComponent,
    separator: separatorComponent
  },
  mixins: [itemMixin],
  created: function(){
  },
  mounted: function(){
    for(var i=0; i<3; i++) this.randomID.push('image-align-'+getRandomString(6,'numeric'));
  },
  computed: {
    // ALTの入力を許可するか
    isAltEnabled: function () {
      return editor.options.allowImageAlt;
    }
  },
  methods: {
    // ドラッグ&ドロップ処理が可能か
    canDrop: function(){
      return this.isDndSupported && !droppingItem; // 要素のドラッグ&ドロップ処理でなければドロップ処理可
    },

    // アップローダボタンクリック
    selectFile: function(ev){
      // アップローダの表示
      ArticleEditor.showUploader(
        editor.options.uploaderDomain,
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        this.setImage
      );
      return false;
    },

    // VE上でのテキスト入力イベント
    onTextChange: function(text){
      this.item.text = text;
    },

    // アップロードファイル指定
    setImage: function(path){
      var acceptable = editor.options.imageTypes;
      if( path && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !path.match( reg ) ){
          alert('画像の種類は ' + acceptable.join('・') + ' のいずれかである必要があります');
          return false;
        }
      }

      this.item.image = path;
    },

    // ファイルの削除
    onFileDelete: function(){
      if( !confirm('画像をクリアします。よろしいですか？\n(アップロードされたファイルは削除されません)') ) return false;
      this.setImage( null );
    },

    // VEの初期化完了
    onVEInit: function(){
      this.item.veInited = true;
    },

    // ファイルのドラッグオーバー
    onDragover: function(ev){
      if( !this.canDrop() ) return false;

      ev.dataTransfer.dropEffect = 'copy';
      this.dragover = true;
    },

    // ファイルのドラッグリーブ
    onDragleave: function(ev){
      if( !this.canDrop() ) return false;

      this.dragover = false;
    },

    // ファイルのドロップ
    onDrop: function(ev){
      if( !this.canDrop() ) return false;

      var self = this;
      this.dragover = false;
      this.isLoadingFile = true;
      this.uploadProgress = 0;

      // ドロップされたファイルのチェック
      var files = ev.dataTransfer.files;
      file_count = files.length;

      var error = '';
      if( file_count == 0 ) error = '画像が指定されていません';
      else if( file_count > 1 ) error = 'アップロードできる画像は1個までです';

      var file = files[0];
      if( !error && file.size > sizeToByte(editor.options.maxFileSize) ) error = '画像のサイズが上限('+editor.options.maxFileSize+')を超過しています';

      var acceptable = editor.options.imageTypes;
      if( !error && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !file.name.match( reg ) ){
          error = '画像の種類は ' + acceptable.join('・') + ' のいずれかである必要があります';
        }
      }
      if( error ){
        this.isLoadingFile = false;
        alert( error );
        return false;
      }

      // ファイル送信成功
      var onSuccess = function(file){
        self.isLoadingFile = false;
        self.setImage(file); // 画像のセット
      };
      // ファイル送信失敗
      var onError = function(err){
        self.isLoadingFile = false;
        alert(err); // エラーを表示
      };
      var progress = function(progress){
        self.uploadProgress = progress*100;
      };

      // ファイルの送信処理
      ArticleEditor.upload.call(
        this,
        file,
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        onSuccess,
        onError,
        progress
      );

    },

    onFocus: function(){
      this.focus = true;
    },

    onBlur: function(){
      this.focus = false;
    }
  }
};
    // リンクコンポーネント ///////////////////////////////////////////////////
var linkComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item link" v-bind:class="{dragover:dragover, dragging:dragging}" v-on:dragover.prevent.stop="onDragover" v-on:dragleave="onDragleave" v-on:drop.prevent.stop="onDrop">\
      <div class="loading" v-if="isLoadingFile"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">リンク</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="wrap">\
          <ul>\
            <li><label>URL</label><input type="text" placeholder="http://www.example.com/" class="url" v-model="item.url" v-on:keydown.enter.prevent="" /></li>\
            <li><label>テキスト</label><input type="text" placeholder="リンクに設定するテキストを入力" class="text" v-model="item.text" v-on:keydown.enter.prevent="" /></li>\
            <li class="link-target"><label><input type="checkbox" v-model="item.newWindow" />別ウィンドウで開く</label></li>\
          </ul>\
        </div>\
        <button class="uploader" tabindex="-1" v-on:click.prevent="onUploaderClick">アップロード済みのファイルから選択</button>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    separator: separatorComponent
  },
  mixins: [itemMixin],
  data: function(){
    return {
      dragover: false,
      isDndSupported: editor.options.uploaderDomain ? false : true,
      isLoadingFile: false,
      uploadProgress: 0
    }
  },
  methods: {
    // ドラッグ&ドロップ処理が可能か
    canDrop: function(){
      return this.isDndSupported && !droppingItem; // 要素のドラッグ&ドロップ処理でなければドロップ処理可
    },

    // アップローダボタンクリック 
    onUploaderClick: function(ev){
      // アップローダの表示
      ArticleEditor.showUploader( 
        editor.options.uploaderDomain, 
        editor.options.uploaderPath, 
        editor.options.uploaderDir, 
        this.setFile 
      );
      return false;
    },

    // アップロードファイル指定
    setFile: function(path){
      var acceptable = editor.options.fileTypes;
      if( acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !path.match( reg ) ){
          alert('ファイルの種類は ' + acceptable.join('・') + ' のいずれかである必要があります');
          return false;
        }
      }

      this.item.url = path;
    },

    // ファイルのドラッグオーバー
    onDragover: function(ev){
      if( !this.canDrop() ) return false;

      ev.dataTransfer.dropEffect = 'copy';
      this.dragover = true;
    },

    // ファイルのドラッグリーブ
    onDragleave: function(ev){
      if( !this.canDrop() ) return false;

      this.dragover = false;
    },

    // ファイルのドロップ
    onDrop: function(ev){
      if( !this.canDrop() ) return false;

      var self = this;
      this.dragover = false;
      this.isLoadingFile = true;
      this.uploadProgress = 0;

      // ドロップされたファイルのチェック
      var files = ev.dataTransfer.files;
      file_count = files.length;

      var error = '';
      if( file_count == 0 ) error = 'ファイルが指定されていません';
      else if( file_count > 1 ) error = 'アップロードできるファイルは1個までです';

      var file = files[0];
      if( !error && file.size > sizeToByte(editor.options.maxFileSize) ) error = 'ファイルのサイズが上限('+editor.options.maxFileSize+')を超過しています';

      var acceptable = editor.options.fileTypes;
      if( !error && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !file.name.match( reg ) ){
          error = 'ファイルの種類は ' + acceptable.join('・') + ' のいずれかである必要があります';
        }
      }
      if( error ){
        this.isLoadingFile = false;
        alert( error );
        return false;
      }

      // ファイル送信成功
      var onSuccess = function(file){
        self.isLoadingFile = false;
        self.setFile(file); // ファイルのセット
      };
      // ファイル送信失敗
      var onError = function(err){
        self.isLoadingFile = false;
        alert(err); // エラーを表示
      };
      var progress = function(progress){
        self.uploadProgress = progress*100;
      };

      // ファイルの送信処理
      ArticleEditor.upload(
        file, 
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        onSuccess,
        onError,
        progress
      );

    }
  }
};
    // 項目セパレータ
var listSeparatorComponent = {
  template: '\
    <div class="list-separator" v-bind:class="{dragover:dragover}" v-on:dragover.prevent.stop="onDragover" v-on:dragleave="onDragleave" v-on:drop.prevent.stop="onDrop"></div>',
  props: ['droppingRow'],
  data: function(){
    return {
      dragover: false
    };
  },
  methods: {
    // ドラッグ&ドロップ処理が可能か
    canDrop: function(){
      return this.droppingRow ? true : false; // DND操作中の行が存在する場合はドロップ許可
    },
    onDragover: function(ev){
      if( !this.canDrop() ) return false;

      this.dragover = true;
      ev.dataTransfer.dropEffect = 'move';
    },
    onDragleave: function(ev){
      if( !this.canDrop() ) return false;

      this.dragover = false;
    },
    onDrop: function(ev){
      if( !this.canDrop() ) return false;

      this.dragover = false;
      this.$emit('row-drop');
    }
  }
};

// リストコンポーネント ///////////////////////////////////////////////////
var listRowComponent = {
  template: '\
  <div class="list-row-wrap">\
    <div class="row" v-bind:class="{dragging:dragging, focus:focus}" v-bind:draggable="draggable" v-on:dragstart="onRowDragstart" v-on:dragend="onRowDragend">\
      <label v-on:mouseover="onLabelMouseover" v-on:mouseout="onLabelMouseout"></label>\
      <input type="text" v-model="row.text" v-on:focus="onFocus" v-on:blur="onBlur" v-on:keydown.enter.prevent="" />\
      <button class="del" tabindex="-1" v-on:click.prevent="onRowDelete"></button>\
    </div>\
    <listSeparator v-bind:droppingRow="droppingRow" v-on:row-drop="onRowDrop" />\
  </div>',
  props: ['row', 'droppingRow'],
  components: {
    listSeparator: listSeparatorComponent
  },
  data: function(){
    return {
      dragging: false,
      draggable: false,
      focus: false
    };
  },
  methods: {
    onRowDelete: function(){
      this.$emit('row-delete', this.row);
    },
    onLabelMouseover: function(){
      this.draggable = true;
    },
    onLabelMouseout: function(){
      this.draggable = false;
    },
    onRowDragstart: function(ev){
      // _d('label dragstart');

      ev.dataTransfer.setData('text', 'element');
      ev.dataTransfer.effectAllowed = 'move';

      this.dragging = true;
      this.$emit('row-dragstart', this.row);
    },
    onRowDragend: function(ev){
      this.dragging = false;
      this.$emit('row-dragend', this.row);
    },
    onRowDrop: function(row){
      this.dragging = false;
      this.$emit('row-drop', this.row);
    },
    onFocus: function(){
      this.focus = true;
    },
    onBlur: function(){
      this.focus = false;
    }
  }
};

// リスト
var listComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item list" v-bind:class="{dragging:dragging}">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">リスト</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="list-wrap">\
          <listSeparator v-bind:droppingRow="droppingRow" v-on:row-drop="onRowDrop" />\
          <transition-group name="list-row" tag="div">\
          <row v-for="(row, index) in item.rows" v-bind:row=row v-bind:droppingRow="droppingRow" :key="row.id" v-on:row-delete="deleteRow" v-on:row-dragstart="onRowDragstart" v-on:row-dragend="onRowDragend" v-on:row-drop="onRowDrop" />\
          </transition-group>\
        </div>\
        <div class="ctl">\
          <button class="add" tabindex="-1" v-on:click.prevent="addRow">行の追加</button>\
        </div>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    row: listRowComponent,
    separator: separatorComponent,
    listSeparator: listSeparatorComponent
  },
  data: function(){
    return {
      droppingRow: null
    };
  },
  mixins: [itemMixin],
  methods: {
    // 行の追加
    addRow: function(){
      var rand = getRandomString();
      console.log(rand);
      this.item.rows.push({ component: 'row', text: null, id: rand});
      console.log(this.item.rows)
    },
    // 行の削除
    deleteRow: function(del_row){
      if( del_row.text && !confirm('行を削除します。よろしいですか？') ) return false;

      var pos = this.item.rows.indexOf(del_row);
      if( pos === -1 ) return false;

      if( this.item.rows.length <= 1 ){ // 残り1行の場合
        this.item.rows[0].text = null;
      }else{
        this.item.rows.splice(pos,1); // 削除対象の行を削除
      }

    },

    // 行のドラッグスタート（並び替え）
    onRowDragstart: function(row){
      this.droppingRow = row; // ドラッグ中の行を記録
    },

    // 要素のドラッグエンド
    onRowDragend: function(row){
      this.droppingRow = null;  // ドラッグ中の行をリセット
    },

    // 行のドロップ
    onRowDrop: function(row){
      if( !this.droppingRow ) return false;

      var dest_pos = row ? this.item.rows.indexOf(row)+1 : 0;
      var src_pos = this.item.rows.indexOf(this.droppingRow);
      this.droppingRow = null; // ドラッグ中の行をリセット

      // 移動先が今から変更がなければスキップ
      if( dest_pos===src_pos || dest_pos===src_pos+1 ) return false;

      // 要素を入れ替え
      var move_row = this.item.rows.splice( src_pos, 1 );
      dest_pos = row ? this.item.rows.indexOf(row)+1 : 0;
      this.item.rows.splice(dest_pos, 0, move_row[0]);

    }
  
  }
};
    var tableMenuComponent = {
  template : '\
    <div class="table-menu" v-bind:class="[data.direction,{show: data.show}]" ref="tableMenu">\
      <ul>\
        <li v-bind:class="{show: data.mode==\'col\'}" v-on:click="onMenuClick" data-action="insert-left">左に列を挿入</li>\
        <li v-bind:class="{show: data.mode==\'col\'}" v-on:click="onMenuClick" data-action="insert-right">右に列を挿入</li>\
        <li v-bind:class="{show: data.mode==\'row\'}" v-on:click="onMenuClick" data-action="insert-above">上に行を挿入</li>\
        <li v-bind:class="{show: data.mode==\'row\'}" v-on:click="onMenuClick" data-action="insert-below">下に行を挿入</li>\
        <li v-bind:class="{show: data.mode==\'row\'}" v-on:click="onMenuClick" data-action="header-row">行を見出しにする</li>\
        <li v-bind:class="{show: data.mode==\'col\'}" v-on:click="onMenuClick" data-action="header-col">列を見出しにする</li>\
        <li v-bind:class="{show: data.mode==\'row\'}" v-on:click="onMenuClick" data-action="de-header-row">行の見出しを解除</li>\
        <li v-bind:class="{show: data.mode==\'col\'}" v-on:click="onMenuClick" data-action="de-header-col">列の見出しを解除</li>\
        <li v-bind:class="{show: data.mode==\'row\'}" v-on:click="onMenuClick" data-action="delete-row">行を削除</li>\
        <li v-bind:class="{show: data.mode==\'col\'}" v-on:click="onMenuClick" data-action="delete-col">列を削除</li>\
      </ul>\
    </div>',
  props: ['data'],
  methods: { 
    onMenuClick: function(ev){
      var action = $(ev.target).attr('data-action');
      this.$emit('menu-click', action);
    }
  }
};

// 列見出しコンポーネント
var tableColHeaderComponent = {
  props: ['colNo'],
  data: function(){
    return {
      dragging: false,
      draggable: false
    };
  },
  template: '\
    <th\
      v-bind:draggable="draggable"\
      v-bind:class="{dragging:dragging}"\
      v-on:dragstart="onDragstart"\
      v-on:dragover="onDragover"\
      v-on:dragend="onDragend"\
      v-on:mouseover="onMouseover"\
      v-on:mouseout="onMouseout">\
      <div \
        class="menu col-menu"\
        v-on:click.prevent.stop="onMenuClick(colNo-1,$event)"></div>\
    </th>',
  methods: {
    onMenuClick: function(col, ev){
      this.$emit('menu-click',col, ev);
    },
    onMouseover: function(){
      this.draggable = true;
    },
    onMouseout: function(){
      this.draggable = false;
    },
    onDragstart: function(ev){
      ev.dataTransfer.setData('text', 'element');
      ev.dataTransfer.effectAllowed = 'move';

      if(ev.dataTransfer.setDragImage){
        // 空のドラッグイメージの設定
        // (Safariは指定しないとdragstartが正しく動作しないため)
        var img = new Image();
        img.src = SCRIPT_DIR_PATH + 'img/blank.png';
        ev.dataTransfer.setDragImage(img,0,0);
      }

      this.dragging = true;
      this.$emit('col-dragstart', this.colNo-1, ev);
    },
    onDragend: function(ev){
      this.dragging = false;
      this.$emit('col-dragend', this.colNo-1, ev);
    },
    onDragover: function(ev){
      this.$emit('col-dragover', ev);
    }
  }
};

// テーブル列コンポーネント ///////////////////////////////////////////////////
var tableColComponent = {
  template: '\
  <td \
    class="ve-style"\
    v-bind:class="{header: col.header}"\
    v-on:click="onClick"\
    ref="inputElement"></td>\
  ',
  props: ['col'],
  mounted: function(){
    var $input = $(this.$refs.inputElement);
    if( this.col.text ) $input.html(this.col.text);
    else $input.html('<br>');
  },
  watch: {
    'col.text': function(val){ // テキストが変更された
      var $input = $(this.$refs.inputElement);
      $input.html(val);
    }
  },
  methods: {
    onClick: function(){
      this.$emit('editstart', this.col);
    }
  }
};

// テーブル行コンポーネント ///////////////////////////////////////////////////
var tableRowComponent = {
  template: '\
  <tr \
    v-bind:draggable="draggable"\
    v-on:dragstart="onDragstart"\
    v-on:dragover="onDragover"\
    v-on:dragend="onDragend">\
    <th \
      v-bind:class="{dragging:dragging}"\
      v-on:mouseover="onMouseover"\
      v-on:mouseout="onMouseout"><div class="menu row-menu" v-on:click.prevent.stop="onMenuClick"></div></th>\
    <column\
      v-for="(col, index) in row.cols"\
      v-bind:col=col\
      v-on:editstart="onEditStart"\
      :key="col" />\
  </tr>',
  props: ['row', 'droppingRow'],
  components: {
    listSeparator: listSeparatorComponent,
    column: tableColComponent
  },
  data: function(){
    return {
      dragging: false,
      draggable: false
    };
  },
  // watch: {
  //   'row.cols.0': function(val){
  //     console.log(val);
  //   }
  // },
  methods: {
    // 編集の開始
    onEditStart: function(col){
      this.$emit('editstart', this.row, col);
    },
    onMenuClick: function(ev){
      this.$emit('row-menu-click', this.row, ev);
    },
    onMouseover: function(){
      this.draggable = true;
    },
    onMouseout: function(){
      this.draggable = false;
    },
    onDragstart: function(ev){
      ev.dataTransfer.setData('text', 'element');
      ev.dataTransfer.effectAllowed = 'move';

      if(ev.dataTransfer.setDragImage){
        // 空のドラッグイメージの設定
        // (Safariは指定しないとdragstartが正しく動作しないため)
        var img = new Image();
        img.src = SCRIPT_DIR_PATH + 'img/blank.png';
        ev.dataTransfer.setDragImage(img,0,0);
      }

      this.dragging = true;
      this.$emit('row-dragstart', this.row, ev);
    },
    onDragover: function(ev){
      this.$emit('row-dragover', ev);
    },
    onDragend: function(ev){
      this.dragging = false;
      this.$emit('row-dragend', this.row, ev);
    }
  }
};

var tableComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item table" v-bind:class="">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">表組み</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="size">\
          <label>横</label><input v-model="colNumInput" type="text"> × <label>縦</label><input v-model="rowNumInput" type="text"><button tabindex="-1" class="apply" v-on:click.prevent="applyChange">適用</button>\
        </div>\
        <div class="table-wrap">\
          <table ref="table">\
            <thead>\
              <tr>\
                <th></th>\
                <colHeader \
                  v-for="col in colNum"\
                  v-bind:colNo=col\
                  :key="col"\
                  v-on:menu-click="onColMenuClick"\
                  v-on:col-dragstart="onColDragstart"\
                  v-on:col-dragend="onColDragend"\
                  v-on:col-dragover="onColDragover">\
              </tr>\
            </thead>\
            <tbody>\
              <row \
                v-for="(row, index) in item.rows"\
                v-bind:row=row :key="row"\
                v-on:editstart="onEditStart"\
                v-on:dragover.prevent.stop="onDragover"\
                v-on:row-menu-click="onRowMenuClick"\
                v-on:row-dragstart="onRowDragstart"\
                v-on:row-dragend="onRowDragend"\
                v-on:row-dragover="onRowDragover"\
                 />\
            </tbody>\
          </table>\
          <div class="col-button-wrap"><button class="add add-col" v-on:click.prevent="addCol()" v-bind:disabled="isMaxCol">列</button></div>\
          <div class="line-selection" ref="lineSelection"></div>\
          <div class="insert-line" ref="insertLine"></div>\
          <div class="dragging-selection" ref="draggingSelection"></div>\
        </div>\
        <button class="add add-row" v-on:click.prevent="addRow()" v-bind:disabled="isMaxRow">行</button>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <component\
      class="table-ve"\
      v-bind:is="inputComponent"\
      v-bind:isPlaceholderEnabled=false\
      v-bind:text=item.text\
      v-on:input="onTextChange"\
      v-on:ve-init="onVEInit"\
      ref="celleditor" />\
    <div class="ve-overlay" v-on:click="onEditEnd" ref="veOverlay"></div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    row: tableRowComponent,
    colHeader: tableColHeaderComponent,
    visualeditor: VEComponent,
    plaintexteditor: plainTextInputComponent,
    separator: separatorComponent
  },
  data: function(){
    return {
      inputComponent: editor.options.inlineVe ? 'visualeditor' : 'plaintexteditor',
      prevDraggingTime: 0,                                // ドラッグ処理の前回処理時間
      colNumInput: editor.options.tableDefaultColNum,     // 入力用列数
      rowNumInput: 1,                                     // 入力用行数
      actionPos: 0,                                       // テーブルメニューの操作対象となる列・行の位置
      isDraggingRow: false,                               // ドラッグ中の行データ
      isDraggingCol: false,                               // ドラッグ中の列データ
      dropRowPos: -1,                                     // 行のドロップ位置
      dropColPos: -1,                                     // 列のドロップ位置
      editingCol: null                                    // 編集中の列データ
    };
  },
  created: function(){
    if(!this.item.rows || this.item.rows.length==0){
      // 一行もデータがなければ空行を追加
      this.addRow();
    }
    this.syncInput();

    // ドラッグ時の画像をプリロード（for Safari）
    var img = new Image();
    img.src = SCRIPT_DIR_PATH + 'img/blank.png';
  },
  mounted: function(){
    var $celleditor = $(this.$refs.celleditor.$el);
    $celleditor.hide(); // VisualEditorを非表示に

    // セル内のリンク先に遷移しないようにする
    var $self = $(this.$el);
    $self.on('click', 'a', function(ev){
      ev.stopPropagation();
      ev.preventDefault();
    });
  },
  mixins: [itemMixin],
  computed: {
    // 行数
    rowNum: function(){
      if(!this.item.rows) return 0;
      else return this.item.rows.length;
    },
    // 列数
    colNum: function(){
      if(!this.item.rows || this.item.rows.length==0) return 0;
      else return this.item.rows[0].cols.length;
    },
    // 最大行数に達している
    isMaxRow: function(){
      return this.rowNum >= editor.options.tableMaxRow;
    },
    isMaxCol: function(){
      return this.colNum >= editor.options.tableMaxCol;
    }
  },
  methods: {
    // 列、行数の確定
    applyChange: function(){
      // 全角数字の変換
      this.rowNumInput = parseInt(String(this.rowNumInput).replace(/[０-９]/g, function(s){
        return String.fromCharCode(s.charCodeAt(0)-65248);
      }));
      this.colNumInput = parseInt(String(this.colNumInput).replace(/[０-９]/g, function(s){
        return String.fromCharCode(s.charCodeAt(0)-65248);
      }));

      if(this.rowNumInput<=0 || this.rowNumInput>editor.options.tableMaxRow){
        alert('テーブルの行数は 1〜'+editor.options.tableMaxRow+' の範囲で設定可能です');
        return false;
      }
      if(this.colNumInput<=0 || this.colNumInput>editor.options.tableMaxCol){
        alert('テーブルの列数は 1〜'+editor.options.tableMaxCol+' の範囲で設定可能です');
        return false;
      }

      if(!this.rowNumInput){ // 行数が入力されていない
        this.rowNumInput = 1;
      }
      if(!this.colNumInput){ // 列数が入力されていない
        this.colNumInput = editor.options.tableDefaultColNum;
      }
      // _d({rowNum:this.rowNum, rowNumInput:this.rowNumInput, colNum:this.colNum, colNumInput:this.colNumInput});
      if(this.rowNum > this.rowNumInput || this.colNum > this.colNumInput){
        // 現在の列・行数よりも少ない値が入力された
        if(!confirm('現在の入力エリアよりも縮小するため、データが失われます。よろしいですか？')){
          // 入力を元に戻す
          return false;
        }
      }

      if(this.rowNumInput > this.rowNum){ // 行を増やす
        var num = this.rowNumInput - this.rowNum;
        for(var i=0; i<num; i++) this.addRow(null);
      }else if(this.rowNumInput < this.rowNum){ // 行を減らす
        var num = this.rowNum - this.rowNumInput;
        for(var i=0; i<num; i++) this.deleteRow(null);
      }

      if(this.colNumInput > this.colNum){ // 列を増やす
        var num = this.colNumInput - this.colNum;
        for(var i=0; i<num; i++) this.addCol(null);
      }else if(this.colNumInput < this.colNum){ // 列を減らす
        var num = this.colNum - this.colNumInput;
        for(var i=0; i<num; i++) this.deleteCol(null);
      }
    },
    // VE上でのテキスト入力イベント
    onTextChange: function(text){
    },
    // VEの初期化完了
    onVEInit: function(){
    },
    // 編集の開始
    onEditStart: function(row, col){
      var $celleditor = $(this.$refs.celleditor.$el);
      var $overlay = $(this.$refs.veOverlay);
      var $table = $(this.$refs.table);
      var $editor = $(editor.vue.$el);  // ArticleEditor自身
      
      // 編集セルのDOMを取得
      var rowIndex = this.rowIndex(row);
      var colIndex = this.colIndex(col);
      if(rowIndex<0 || colIndex<0) return false;

      var $row = $table.find('tbody tr').eq(rowIndex);
      var $col = $row.find('td').eq(colIndex);

      // 編集セルの相対位置を計算（ArticleEditor自身を基準に）
      var width = $col.width();
      var height = $col.outerHeight()-2;
      var x = $col.offset().left - $editor.offset().left
      var y = $row.offset().top - $editor.offset().top;
      if(height < 80) height = 80;  // 高さが低すぎるとVisualEditorのメニューの表示上不具合がある

      // _d({width:width, height:height, x:x, y:y});

      // VisualEditorの表示処理
      $celleditor
        .width(width)
        .css({left:x+'px', top:y+'px', minHeight:height+'px'})
        .html(col.text)
        .show();

      // 時間差でフォーカス処理を行う
      setTimeout(function(){
        $celleditor.trigger('focus');
      },0);

      $overlay.show(); // 入力エリア以外のクリック検知のためのオーバーレイ表示

      // 入力エリア外のクリックを画面全体で検知するため、
      // 入力エリアとオーバーレイをArticleEditor直下に移動する
      $editor
        .append($overlay)
        .append($celleditor);

      // 編集セルを記録
      this.editingCol = col;
    },
    // 編集の終了
    onEditEnd: function(ev){
      if(!this.editingCol) return false;

      var $celleditor = $(this.$refs.celleditor.$el);
      var $overlay = $(this.$refs.veOverlay);
      var $self = $(this.$el);

      // 入力されたデータをセルに反映
      this.editingCol.text = $celleditor.html();

      // VisualEditorを非表示
      $celleditor
        .trigger('blur')
        .hide(); 
      // オーバーレイを非表示
      $overlay.hide();

      // VisualEditorとオーバーレイを元の位置に戻す
      $self
        .append($celleditor)
        .append($overlay);

      this.editingCol = null;
    },
    // 行見出しのメニューボタンクリック
    onRowMenuClick: function(row, ev){
      var row_no = null;
      for(var i=0; i<this.rowNum; i++){
        if(row==this.item.rows[i]){// クリックされた行の検出
          row_no = i;
          this.onMenuClick(row_no,'row',ev);
        }
      }
      if(row_no!=null){
        this.showLineSelection('row',row_no); // 選択表示
      }
    },
    // 列見出しのメニューボタンクリック
    onColMenuClick: function(pos, ev){
      this.onMenuClick(pos,'col',ev);

      this.showLineSelection('col',pos); // 選択表示
    },
    // メニューの表示
    onMenuClick: function(pos,mode,ev){
      this.actionPos = pos;
      this.$emit('table-header-menu-click',this,mode,ev);
    },
    // テーブル操作実行
    doAction: function(action){
      switch(action){
        case 'insert-left': // 左に列挿入
          this.addCol(this.actionPos);
          break;
        case 'insert-right': // 右に列挿入
          this.addCol(this.actionPos+1);
          break;
        case 'insert-above': // 上に行挿入
          this.addRow(this.actionPos);
          break;
        case 'insert-below': // 下に行挿入
          this.addRow(this.actionPos+1);
          break;
        case 'delete-row': // 行の削除
          if(confirm('行を削除します。よろしいですか?')) this.deleteRow(this.actionPos);
          break;
        case 'delete-col': // 列の削除
          if(confirm('列を削除します。よろしいですか?')) this.deleteCol(this.actionPos);
          break;
        case 'header-row': // 行の見出し化
          this.headerRow(this.actionPos,true);
          break;
        case 'header-col': // 列の見出し化
          this.headerCol(this.actionPos,true);
          break;
        case 'de-header-row': // 行の見出し化解除
          this.headerRow(this.actionPos,false);
          break;
        case 'de-header-col': // 列の見出し化解除
          this.headerCol(this.actionPos,false);
          break;
        default: 
          break;
      }
      this.showLineSelection();
    },
    // 行の追加
    addRow: function(pos){
      if(this.item.rows.length >= editor.options.tableMaxRow) return false; // 最大行数に達している場合は何もしない
      if(pos==null) pos = this.rowNum; // 行番号が指定されなければ最終行に追加

      var cols = [];
      var col_num = this.colNum ? this.colNum : editor.options.tableDefaultColNum; 
      for(var i=0; i<col_num; i++){
        cols.push({text: '', header:this.isHeaderCol(i)});
      }
      if(!this.item.rows) this.item.rows = [];
      this.item.rows.splice(pos, 0, {cols: cols});

      this.syncInput('row');
    },
    // 列の追加
    addCol: function(pos){
      if(this.item.rows && this.item.rows[0].cols.length >= editor.options.tableMaxCol) return false; // 最大列数に達している場合は何もしない
      if(pos==null) pos = this.colNum; // 列番号が指定されなければ最終列に追加

      var row_len = this.item.rows.length;
      var flg_header = [];
      for(var i=0; i<row_len; i++){
        flg_header[i] = this.isHeaderRow(i);
      }
      for(var i=0; i<row_len; i++){
        var row = this.item.rows[i];
        row.cols.splice(pos, 0, {text: '', header:flg_header[i]});
      }

      this.syncInput('col');
    },

    // 行の削除
    deleteRow: function(pos){
      if(pos==null) pos = this.rowNum-1;
      this.item.rows.splice(pos, 1);

      if(this.rowNum == 0){ // 全削除
        this.addRow(null); // 空行を追加
      }
      this.syncInput('row');
    },

    // 列の削除
    deleteCol: function(pos){
      if(pos==null) pos = this.colNum-1;
      for(var i=0; i<this.rowNum; i++){
        this.item.rows[i].cols.splice(pos, 1);
      }

      if(this.colNum == 0){ // 全削除
        this.addCol(null); // 空列を追加
      }
      this.syncInput('col');
    },

    // 行を見出し化する
    headerRow: function(pos, onoff){
      for(var i=0; i<this.colNum; i++){
        // 列全体が見出しになっている場合は、その列は見出しを解除しない
        // if(onoff==false && this.isHeaderCol(i)) continue;
        this.item.rows[pos].cols[i].header = onoff;
      }
    },

    // 列を見出し化する
    headerCol: function(pos, onoff){
      for(var i=0; i<this.rowNum; i++){
        // 行全体が見出しになっている場合は、その行は見出しを解除しない
        // if(onoff==false && this.isHeaderRow(i)) continue;
        this.item.rows[i].cols[pos].header = onoff;
      }
    },
    // 見出し行かどうか
    isHeaderRow: function(row_num){
      var header_count = 0;
      for(var i=0; i<this.colNum; i++){
        if(this.item.rows[row_num].cols[i].header) header_count++;
      }
      return this.colNum>0 && this.colNum == header_count;
    },
    // 見出し列かどうか
    isHeaderCol: function(col_num){
      var header_count = 0;
      for(var i=0; i<this.rowNum; i++){
        if(this.item.rows[i].cols[col_num].header) header_count++;
      }
      return this.rowNum>0 && this.rowNum == header_count;
    },

    // 実際の行・列数と入力フィールドの値を同期する
    syncInput: function(mode){
      if(mode=='row'){ this.rowNumInput = this.rowNum; }
      else if(mode=='col'){ this.colNumInput = this.colNum; }
      else { this.colNumInput = this.colNum; this.rowNumInput = this.rowNum; }
    },

    // 行・列の選択処理
    showLineSelection: function(mode, no){
      var $selection = $(this.$refs.lineSelection);
      var $table = $(this.$refs.table);

      if(typeof mode == 'undefined'){ // 非表示処理
        $selection.hide();
      }else{ // 表示処理
        if(mode=='row'){
          var $row = $table.find('tr').eq(no+1);
          var x = $table.position().left + $row.position().left;
          var y = $table.position().top + $row.position().top;
          $selection
            .show()
            .width($row.outerWidth()-1)
            .height($row.outerHeight()-1)
            .css({left: x+'px', top: y+'px'});
        }else if(mode=='col'){
          var $col = $table.find('tr').eq(0).find('th').eq(no+1);
          var x = $table.position().left + $col.position().left;
          var y = $table.position().top;
          $selection
            .show()
            .width($col.outerWidth()-1)
            .height($table.outerHeight()-2)
            .css({left: x+'px', top: y+'px'});
        }
      }
    },

    // 行のドラッグが開始された
    onRowDragstart: function(row, ev){
      var $insert = $(this.$refs.insertLine);
      var $sel = $(this.$refs.draggingSelection);
      var $table = $(this.$refs.table);
      var $self = $(this.$el);

      // ドラッグ位置を示すボーダーを表示設定
      $insert
        .show()
        .width($table.width())
        .height('2px')
        .css({left: $table.position().left+'px'});

      // ドラッグ対象を示す選択範囲を表示設定
      if(ev.dataTransfer.setDragImage){
        var row_pos = -1;
        for(var i=0; i<this.rowNum; i++){
          if(this.item.rows[i] == row){
            row_pos = i;
            break;
          }
        }
        var row_h = $table.find('tbody tr').eq(row_pos).outerHeight();
        var row_x = $table.position().left + $table.find('thead tr th').eq(0).outerWidth();
        $sel
          .show()
          .width($table.width() - $table.find('thead tr th').eq(0).outerWidth())
          .height(row_h)
          .css({left: row_x+'px'});
      }

      this.isDraggingRow = true;
      this.onRowDragover(ev);
    },
    // 行のドラッグ中
    onRowDragover: function(ev){
      if(!this.isDraggingRow) return;

      // 一定の間隔以下のドラッグ処理はスキップ
      var t = new Date().getTime();
      var pt = this.prevDraggingTime;
      if(t - pt < 20) return;
      this.prevDraggingTime = t;

      // ドラッグ位置を示すボーダーを表示
      var $insert = $(this.$refs.insertLine);
      var $table = $(this.$refs.table);
      var $sel = $(this.$refs.draggingSelection);

      var pos = this.draggingRowPosition(ev);
      var y;

      if(pos >= this.rowNum) y = $table.position().top + $table.find('tbody tr').eq(pos-1).offset().top - $table.offset().top + $table.find('tbody tr').eq(pos-1).outerHeight();
      else y = $table.position().top + $table.find('tbody tr').eq(pos).offset().top - $table.offset().top;
      $insert.css({top: y+'px'});

      y = $table.position().top + ev.pageY - $table.offset().top - $sel.height()/2;
      $sel.css({top: y+'px'});

      this.dropRowPos = pos; // ドラッグ位置を記録
    },
    // 行のドラッグ終了
    onRowDragend: function(row, ev){

      // ドラッグ位置を示すボーダーを非表示
      var $insert = $(this.$refs.insertLine);
      $insert.hide();

      var $sel = $(this.$refs.draggingSelection);
      $sel.hide();

      // ドラッグ位置どドラッグ先の行を入れ替え
      var dest_pos = this.dropRowPos;
      var src_pos = -1;
      for(var i=0; i<this.rowNum; i++){
        if(this.item.rows[i] == row){
          src_pos = i;
          break;
        }
      }
      // _d({src_pos:src_pos, dest_pos:dest_pos});

      if( src_pos==-1 || src_pos==dest_pos) return;

      // ドラッグ元の要素を一旦削除
      this.item.rows.splice(src_pos, 1);
      if(dest_pos > src_pos) dest_pos--;
      // 新しい場所に挿入
      this.item.rows.splice(dest_pos, null, row);

      this.isDraggingRow = false;
    },
    // ドラッグ中の行の位置を取得する
    draggingRowPosition: function(ev){
      var self = this;
      var $table = $(this.$refs.table);
      var pos = -1;

      $first_row = $table.find('tbody tr').eq(0);
      $last_row = $table.find('tbody tr').eq(this.rowNum-1);
      $table.find('tbody tr').each(function(idx){
        var $tr = $(this);
        var top = $tr.offset().top;
        var height = $tr.outerHeight();
        var bottom = top + height;

        if(top < ev.pageY && ev.pageY < bottom){ // 該当の位置にある行を検出
          if(top+height/2 >= ev.pageY) pos = idx;
          if(top+height/2 < ev.pageY) pos = idx+1;
        }else if(ev.pageY <= $first_row.offset().top){
          pos = 0;
        }else if(ev.pageY >= $last_row.offset().top + $last_row.outerHeight()){
          pos = self.rowNum;
        }
      });
      return pos;
    },

    // 列のドラッグが開始された
    onColDragstart: function(col, ev){

      var $insert = $(this.$refs.insertLine);
      var $sel = $(this.$refs.draggingSelection);
      var $table = $(this.$refs.table);

      // ドラッグ位置を示すボーダーを表示設定
      $insert
        .show()
        .height($table.height())
        .width(2)
        .css({top: $table.position().top+'px'});

      if(ev.dataTransfer.setDragImage){
        var col_w = $table.find('thead tr th').eq(col+1).outerWidth();
        var col_h = $table.outerHeight() - $table.find('thead tr').eq(0).outerHeight()-2;
        var col_y = $table.position().top + $table.find('thead tr th').eq(0).outerHeight();

        // ドラッグ対象列の選択要素を表示設定
        $sel
          .show()
          .width(col_w)
          .height(col_h)
          .css({top: col_y+'px'});
      }

      this.isDraggingCol = true;
      this.onColDragover(ev);
    },
    // 列のドラッグ中
    onColDragover: function(ev){
      if(!this.isDraggingCol) return;

      // 一定の間隔以下のドラッグ処理はスキップ
      var t = new Date().getTime();
      var pt = this.prevDraggingTime;
      if(t - pt < 20) return;
      this.prevDraggingTime = t;

      // ドラッグ位置を示すボーダーを表示
      var $insert = $(this.$refs.insertLine);
      var $table = $(this.$refs.table);
      var $sel = $(this.$refs.draggingSelection);

      var pos = this.draggingColPosition(ev);
      var x;

      if(pos >= this.colNum) x = $table.position().left + $table.find('thead tr th').eq(this.colNum).position().left + $table.find('thead tr th').eq(this.colNum-1).outerWidth();
      else x = $table.position().left + $table.find('thead tr th').eq(pos+1).offset().left - $table.offset().left;
      $insert.css({left: x+'px'});

      x = $table.position().left + ev.pageX - $table.offset().left - $sel.width()/2;
      $sel.css({left: x+'px'});

      this.dropColPos = pos; // ドラッグ位置を記録
    },
    // 列のドラッグ終了
    onColDragend: function(colPos, ev){
      // _d('onColDragend');
      // ドラッグ位置を示すボーダーを非表示
      var $insert = $(this.$refs.insertLine);
      $insert.hide();

      var $sel = $(this.$refs.draggingSelection);
      $sel.hide();

      // ドラッグ位置とドラッグ先の列を入れ替え
      var dest_pos = this.dropColPos;
      var src_pos = colPos;
      _d({src_pos:src_pos, dest_pos:dest_pos});

      if(src_pos==-1 || src_pos==dest_pos) return; // ドラッグ対象の列がない、または移動元と移動先が同じなら終了

      if(dest_pos > src_pos) dest_pos--;
      for(var i=0; i<this.rowNum; i++){
        var cols = this.item.rows[i].cols;
        var col = cols.splice(src_pos, 1);
        cols.splice(dest_pos, null, col[0]);
      }

      this.isDraggingCol = false;
    },
    // ドラッグ中の行の位置を取得する
    draggingColPosition: function(ev){
      var self = this;
      var $table = $(this.$refs.table);
      var pos = -1;

      $first_col = $table.find('thead tr th').eq(1);
      $last_col = $table.find('thead tr th').eq(this.colNum);

      $table.find('thead th').each(function(idx){
        var $th = $(this);
        var left = $th.offset().left;
        var width = $th.outerWidth();
        var right = left + width;

        if(left < ev.pageX && ev.pageX < right){ // 該当の位置にある列を検出
          if(left+width/2 >= ev.pageX) pos = idx;
          else if(left+width/2 < ev.pageX) pos = idx+1;
        }else if(ev.pageX <= $first_col.offset().left){
          pos = 1;
        }else if(ev.pageX >= $last_col.offset().left + $last_col.outerWidth()){
          pos = self.colNum+1;
        }
        // _d({idx: idx, left: left, right: right, width:width, x:ev.pageX, y:ev.pageY, pos: pos});

      });
      if(pos>0) pos--; // 一番左の見出し列分を除く
      return pos;
    },
    // 行番号の取得(0〜)
    rowIndex: function(row){
      if(this.rowNum==0) return -1;
      for(var i=0; i<this.rowNum; i++){
        if(this.item.rows[i] == row) return i;
      }
      return -1;
    },
    // 列番号の取得(0〜)
    colIndex: function(col){
      if(this.rowNum==0) return -1;
      for(var i=0; i<this.rowNum; i++){
        var row = this.item.rows[i];
        for(var j=0; j<this.colNum; j++){
          if(row.cols[j]==col) return j;
        }
      }
      return -1;
    }

  }
};

    // HTMLコンポーネント ///////////////////////////////////////////////////
var htmlComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item html" v-bind:class="[item.type, {dragging:dragging}]">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">HTML</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <textarea v-model="item.html" v-on:input="onInput" ref="inputElement" />\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    separator: separatorComponent
  },
  mixins: [itemMixin],
  mounted: function(){
    // テキストエリアの高さ調整のためにinputイベントを強制発火
    // jqueryではなぜかinputイベントが発火しないため
    // 生のdomで実装する
    var elm = this.$refs.inputElement;
    if( document.createEvent ){
      // IE以外
      var evt = document.createEvent('HTMLEvents');
      evt.initEvent('input', true, true ); // event type, bubbling, cancelable
      elm.dispatchEvent(evt);
    }else{
      // IE
      var evt = document.createEventObject();
      elm.fireEvent('oninput', evt);
    }
  },
  computed: {
    labelName: function(){
      return editor.options.typeNames[this.item.type];
    }
  },
  methods: {
    onInput: function(ev){
      // 入力値に応じてリサイズする

      var def_style = window.getComputedStyle(ev.target);
      var line_height = def_style.lineHeight.split('px')[0];
      var padding = def_style.paddingTop.split('px')[0] + def_style.paddingBottom.split('px')[0];

      if( ev.target.scrollHeight > ev.target.offsetHeight ){
        ev.target.style.height = ev.target.scrollHeight+3 + 'px';
      }else{
          
          var height;
          while( true ){
            height = ev.target.style.height.split('px')[0];

            ev.target.style.height = height - line_height +'px';
            if( ev.target.scrollHeight > ev.target.offsetHeight ){
              ev.target.style.height = ev.target.scrollHeight+3 + 'px';
              // _d(ev.target.scrollHeight + padding_top + padding_bottom);
              break;
            }
            break;
          }
      }
      // _d(ev.target.scrollHeight);
      // _d(ev.target.offsetHeight);

    }
  }
};
    // リンクコンポーネント ///////////////////////////////////////////////////
var linkComponent_for_kmu = {
  template: '\
  <div class="item-wrap">\
    <div class="item link" >\
      <div class="loading" v-if="isLoadingFile"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">リンク</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="wrap">\
          <ul>\
            <li><label>URL</label><input type="text" placeholder="http://www.example.com/" class="url" v-model="item.links[0].url" v-on:keydown.enter.prevent="" /></li>\
            <li><label>テキスト</label><input type="text" placeholder="リンクに設定するテキストを入力" class="text" v-model="item.links[0].text" v-on:keydown.enter.prevent="" /></li>\
            <li class="link-target"><label><input type="checkbox" v-model="item.links[0].newWindow" />別ウィンドウで開く</label></li>\
          </ul>\
          <button class="uploader" tabindex="-1" v-on:click.prevent="onUploaderClick(0)">アップロード済みのファイルから選択</button>\
        </div>\
        <div class="wrap">\
          <ul>\
            <li><label>URL</label><input type="text" placeholder="http://www.example.com/" class="url" v-model="item.links[1].url" v-on:keydown.enter.prevent="" /></li>\
            <li><label>テキスト</label><input type="text" placeholder="リンクに設定するテキストを入力" class="text" v-model="item.links[1].text" v-on:keydown.enter.prevent="" /></li>\
            <li class="link-target"><label><input type="checkbox" v-model="item.links[1].newWindow" />別ウィンドウで開く</label></li>\
          </ul>\
          <button class="uploader" tabindex="-1" v-on:click.prevent="onUploaderClick(1)">アップロード済みのファイルから選択</button>\
        </div>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    separator: separatorComponent
  },
  mixins: [itemMixin],
  data: function(){
    return {
      dragover: false,
      isDndSupported: editor.options.uploaderDomain ? false : true,
      isLoadingFile: false,
      uploadProgress: 0
    }
  },
  methods: {
    // ドラッグ&ドロップ処理が可能か
    canDrop: function(){
      return this.isDndSupported && !droppingItem; // 要素のドラッグ&ドロップ処理でなければドロップ処理可
    },

    // アップローダボタンクリック 
    onUploaderClick: function(index){
        
      var _index = index;
      var _this = this;
      // アップローダの表示
      ArticleEditor.showUploader( 
        editor.options.uploaderDomain, 
        editor.options.uploaderPath, 
        editor.options.uploaderDir, 
        function(path){
          var acceptable = editor.options.fileTypes;
          if( acceptable ){
           var reg = new RegExp('\.('+ acceptable.join('|') +')$');
           if( !path.match( reg ) ){
             alert('ファイルの種類は ' + acceptable.join('・') + ' のいずれかである必要があります');
             return false;
           }
          }
          _this.item.links[_index].url = path;
        }
      );
      return false;
    }

  }
};

    // 画像コンポーネント ///////////////////////////////////////////////////
var imageComponent_for_kmu = {
  template: '\
  <div class="item-wrap">\
    <div class="item image" v-bind:class="{\'ve-unload\': !item.veInited, nofile: !item.image, dragover:dragover, dragging:dragging}" v-on:dragover.prevent.stop="onDragover" v-on:dragleave="onDragleave" v-on:drop.prevent.stop="onDrop">\
      <div class="loading" v-if="isLoadingFile"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">画像</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="dnd" v-if="isDndSupported" v-on:dblclick="selectFile">\
          <div class="msg">画像をドラッグ＆ドロップ</div>\
        </div>\
        <div class="content" style="border:none">\
          <div class="img" v-bind:class="item.align" style="margin:0px;padding-right:20px;box-sizing:border-box;">\
            <img v-bind:src="item.image" v-on:dblclick="selectFile" draggable="false" style="margin-right:10px;display:block;" />\
          </div>\
          <div class="img left" style="margin:0px;width:100%;">\
            <label>見出し</label>\
            <input type="text" v-model="item.heading" ref="inputElement" style="margin-bottom:10px" @keydown.enter.prevent="handleEnterKey"/>\
            <label>テキスト</label>\
            <component\
              v-bind:is="inputComponent"\
              v-bind:isPlaceholderEnabled=true\
              class="text"\
              v-bind:class="{focus:focus}"\
              style="border: 1px solid #aaa; padding:8px 5px; min-height:9em;"\
              v-bind:text=item.text\
              v-on:input="onTextChange"\
              v-on:ve-init="onVEInit"\
              v-on:focus="onFocus"\
              v-on:blur="onBlur" />\
              <div class="wrap" style="margin-top:10px">\
                <ul>\
                  <li><label>リンク(URL)</label><input type="text" placeholder="http://www.example.com/" class="url" v-model="item.link_url" v-on:keydown.enter.prevent="" style="margin-top:3px" /></li>\
                  <li style="margin-top:10px"><label>リンク(テキスト)</label><input type="text" placeholder="リンクに設定するテキストを入力" class="text" v-model="item.link_text" v-on:keydown.enter.prevent="" style="margin-top:3px; min-height:1em;" /></li>\
                </ul>\
              </div>\
          </div>\
        </div>\
        <div class="alt-wrap" v-if="isAltEnabled">\
          <label :for="item.id+\'_alt\'">ALT</label>\
          <input type="text" v-model="item.altName" :id="item.id+\'_alt\'" />\
        </div>\
        <button class="remove" tabindex="-1" v-on:click.prevent="onFileDelete">画像をクリア</button>\
        <button class="uploader" tabindex="-1" v-on:click.prevent="selectFile">アップロード済みの画像から選択</button>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  data: function(){
    return {
      randomID: [],
      dragover: false,
      isDndSupported: editor.options.uploaderDomain ? false : true,
      isLoadingFile: false,
      uploadProgress: 0,
      inputComponent: editor.options.inlineVe ? 'visualeditor' : 'plaintexteditor',
      focus: false
    }
  },
  components: {
    visualeditor: VEComponent,
    plaintexteditor: plainTextInputComponent,
    separator: separatorComponent
  },
  mixins: [itemMixin],
  created: function(){
  },
  mounted: function(){
    for(var i=0; i<3; i++) this.randomID.push('image-align-'+getRandomString(6,'numeric'));
  },
  computed: {
    // ALTの入力を許可するか
    isAltEnabled: function () {
      return editor.options.allowImageAlt;
    }
  },
  methods: {
    // ドラッグ&ドロップ処理が可能か
    canDrop: function(){
      return this.isDndSupported && !droppingItem; // 要素のドラッグ&ドロップ処理でなければドロップ処理可
    },

    handleEnterKey() {
      // Enterキーが押されたときの処理
      return true;
    },
    // アップローダボタンクリック
    selectFile: function(ev){
      // アップローダの表示
      ArticleEditor.showUploader(
        editor.options.uploaderDomain,
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        this.setImage
      );
      return false;
    },

    // VE上でのテキスト入力イベント
    onTextChange: function(text){
      this.item.text = text;
    },

    // アップロードファイル指定
    setImage: function(path){
      var acceptable = editor.options.imageTypes;
      if( path && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !path.match( reg ) ){
          alert('画像の種類は ' + acceptable.join('・') + ' のいずれかである必要があります');
          return false;
        }
      }

      this.item.image = path;
    },

    // ファイルの削除
    onFileDelete: function(){
      if( !confirm('画像をクリアします。よろしいですか？\n(アップロードされたファイルは削除されません)') ) return false;
      this.setImage( null );
    },

    // VEの初期化完了
    onVEInit: function(){
      this.item.veInited = true;
    },

    // ファイルのドラッグオーバー
    onDragover: function(ev){
      if( !this.canDrop() ) return false;

      ev.dataTransfer.dropEffect = 'copy';
      this.dragover = true;
    },

    // ファイルのドラッグリーブ
    onDragleave: function(ev){
      if( !this.canDrop() ) return false;

      this.dragover = false;
    },

    // ファイルのドロップ
    onDrop: function(ev){
      if( !this.canDrop() ) return false;

      var self = this;
      this.dragover = false;
      this.isLoadingFile = true;
      this.uploadProgress = 0;

      // ドロップされたファイルのチェック
      var files = ev.dataTransfer.files;
      file_count = files.length;

      var error = '';
      if( file_count == 0 ) error = '画像が指定されていません';
      else if( file_count > 1 ) error = 'アップロードできる画像は1個までです';

      var file = files[0];
      if( !error && file.size > sizeToByte(editor.options.maxFileSize) ) error = '画像のサイズが上限('+editor.options.maxFileSize+')を超過しています';

      var acceptable = editor.options.imageTypes;
      if( !error && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !file.name.match( reg ) ){
          error = '画像の種類は ' + acceptable.join('・') + ' のいずれかである必要があります';
        }
      }
      if( error ){
        this.isLoadingFile = false;
        alert( error );
        return false;
      }

      // ファイル送信成功
      var onSuccess = function(file){
        self.isLoadingFile = false;
        self.setImage(file); // 画像のセット
      };
      // ファイル送信失敗
      var onError = function(err){
        self.isLoadingFile = false;
        alert(err); // エラーを表示
      };
      var progress = function(progress){
        self.uploadProgress = progress*100;
      };

      // ファイルの送信処理
      ArticleEditor.upload.call(
        this,
        file,
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        onSuccess,
        onError,
        progress
      );

    },

    onFocus: function(){
      this.focus = true;
    },

    onBlur: function(){
      this.focus = false;
    }
  }
};

    
// リストコンポーネント ///////////////////////////////////////////////////
var listRowComponent_for_kmu = {
  template: '\
  <div class="list-row-wrap">\
    <div class="row" v-bind:class="{dragging:dragging, focus:focus}" v-bind:draggable="draggable" v-on:dragstart="onRowDragstart" v-on:dragend="onRowDragend">\
      <label v-on:mouseover="onLabelMouseover" v-on:mouseout="onLabelMouseout"></label>\
      <input type="text" v-model="row.text" v-on:focus="onFocus" v-on:blur="onBlur" v-on:keydown.enter.prevent="" placeholder="テキストを入力してください" v-if="!isLink"/>\
      <input type="text" v-model="row.text" v-on:focus="onFocus" v-on:blur="onBlur" v-on:keydown.enter.prevent="" style="width:48%" placeholder="テキストを入力してください" v-if="isLink"/>\
      <input type="text" v-model="row.url" v-on:focus="onFocus" v-on:blur="onBlur" v-on:keydown.enter.prevent="" style="width:47%;padding-left:6px"  placeholder="URLを入力してください" v-if="isLink"/>\
      <button class="del" tabindex="-1" v-on:click.prevent="onRowDelete" style="margin-left: 0.5rem;"></button>\
    </div>\
    <listSeparator v-bind:droppingRow="droppingRow" v-on:row-drop="onRowDrop" />\
  </div>',
  props: ['row', 'droppingRow','isLink'],
  components: {
    listSeparator: listSeparatorComponent
  },
  data: function(){
    return {
      dragging: false,
      draggable: false,
      focus: false
    };
  },
  methods: {
    onRowDelete: function(){
      this.$emit('row-delete', this.row);
    },
    onLabelMouseover: function(){
      this.draggable = true;
    },
    onLabelMouseout: function(){
      this.draggable = false;
    },
    onRowDragstart: function(ev){
      // _d('label dragstart');

      ev.dataTransfer.setData('text', 'element');
      ev.dataTransfer.effectAllowed = 'move';

      this.dragging = true;
      this.$emit('row-dragstart', this.row);
    },
    onRowDragend: function(ev){
      this.dragging = false;
      this.$emit('row-dragend', this.row);
    },
    onRowDrop: function(row){
      this.dragging = false;
      this.$emit('row-drop', this.row);
    },
    onFocus: function(){
      this.focus = true;
    },
    onBlur: function(){
      this.focus = false;
    }
  }
};

// リスト
var listComponent_for_kmu = {
  template: '\
  <div class="item-wrap">\
    <div class="item list" v-bind:class="{dragging:dragging}">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">リスト</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <div class="list-wrap">\
          <listSeparator v-bind:droppingRow="droppingRow" v-on:row-drop="onRowDrop" />\
          <transition-group name="list-row" tag="div">\
          <row v-for="(row, index) in item.rows" :isLink=item.isLink v-bind:row=row v-bind:droppingRow="droppingRow" :key="row.id" v-on:row-delete="deleteRow" v-on:row-dragstart="onRowDragstart" v-on:row-dragend="onRowDragend" v-on:row-drop="onRowDrop" />\
          </transition-group>\
        </div>\
        <div class="ctl">\
          <button class="add" tabindex="-1" v-on:click.prevent="addRow">行の追加</button>\
        </div>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    row: listRowComponent_for_kmu,
    separator: separatorComponent,
    listSeparator: listSeparatorComponent
  },
  data: function(){
    return {
      droppingRow: null
    };
  },
  mixins: [itemMixin],
  methods: {
    // 行の追加
    addRow: function(){
      var rand = getRandomString();
      console.log(rand);
      this.item.rows.push({ component: 'row', text: null, id: rand});
      console.log(this.item.rows)
    },
    // 行の削除
    deleteRow: function(del_row){
      if( del_row.text && !confirm('行を削除します。よろしいですか？') ) return false;

      var pos = this.item.rows.indexOf(del_row);
      if( pos === -1 ) return false;

      if( this.item.rows.length <= 1 ){ // 残り1行の場合
        this.item.rows[0].text = null;
      }else{
        this.item.rows.splice(pos,1); // 削除対象の行を削除
      }

    },

    // 行のドラッグスタート（並び替え）
    onRowDragstart: function(row){
      this.droppingRow = row; // ドラッグ中の行を記録
    },

    // 要素のドラッグエンド
    onRowDragend: function(row){
      this.droppingRow = null;  // ドラッグ中の行をリセット
    },

    // 行のドロップ
    onRowDrop: function(row){
      if( !this.droppingRow ) return false;

      var dest_pos = row ? this.item.rows.indexOf(row)+1 : 0;
      var src_pos = this.item.rows.indexOf(this.droppingRow);
      this.droppingRow = null; // ドラッグ中の行をリセット

      // 移動先が今から変更がなければスキップ
      if( dest_pos===src_pos || dest_pos===src_pos+1 ) return false;

      // 要素を入れ替え
      var move_row = this.item.rows.splice( src_pos, 1 );
      dest_pos = row ? this.item.rows.indexOf(row)+1 : 0;
      this.item.rows.splice(dest_pos, 0, move_row[0]);

    }
  
  }
};

    
// リストコンポーネント ///////////////////////////////////////////////////
var listLinkRowComponent_for_kmu = {
  template: '\
  <div class="list-row-wrap">\
    <div class="row" v-bind:class="{dragging:dragging, focus:focus}" v-bind:draggable="draggable" v-on:dragstart="onRowDragstart" v-on:dragend="onRowDragend">\
      <label v-on:mouseover="onLabelMouseover" v-on:mouseout="onLabelMouseout"></label>\
      <input type="text" v-model="row.text" v-on:focus="onFocus" v-on:blur="onBlur" v-on:keydown.enter.prevent="" style="width:48%" placeholder="テキストを入力してください"/>\
      <input type="text" v-model="row.url" v-on:focus="onFocus" v-on:blur="onBlur" v-on:keydown.enter.prevent="" style="width:47%;padding-left:6px"  placeholder="URLを入力してください"/>\
      <button class="del" tabindex="-1" v-on:click.prevent="onRowDelete" style="margin-left: 0.5rem;"></button>\
    </div>\
    <listSeparator v-bind:droppingRow="droppingRow" v-on:row-drop="onRowDrop" />\
  </div>',
  props: ['row', 'droppingRow'],
  components: {
    listSeparator: listSeparatorComponent
  },
  data: function(){
    return {
      dragging: false,
      draggable: false,
      focus: false
    };
  },
  methods: {
    onRowDelete: function(){
      this.$emit('row-delete', this.row);
    },
    onLabelMouseover: function(){
      this.draggable = true;
    },
    onLabelMouseout: function(){
      this.draggable = false;
    },
    onRowDragstart: function(ev){
      // _d('label dragstart');

      ev.dataTransfer.setData('text', 'element');
      ev.dataTransfer.effectAllowed = 'move';

      this.dragging = true;
      this.$emit('row-dragstart', this.row);
    },
    onRowDragend: function(ev){
      this.dragging = false;
      this.$emit('row-dragend', this.row);
    },
    onRowDrop: function(row){
      this.dragging = false;
      this.$emit('row-drop', this.row);
    },
    onFocus: function(){
      this.focus = true;
    },
    onBlur: function(){
      this.focus = false;
    }
  }
};

// リスト
var listLinkComponent_for_kmu = {
  template: '\
  <div class="item-wrap">\
    <div class="item list" v-bind:class="{dragging:dragging}">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">リンク</label>\
      </div>\
      <div class="col2">\
        <div class="list-wrap">\
          <listSeparator v-bind:droppingRow="droppingRow" v-on:row-drop="onRowDrop" />\
          <transition-group name="list-row" tag="div">\
          <row v-for="(row, index) in item.rows" v-bind:row=row v-bind:droppingRow="droppingRow" :key="row.id" v-on:row-delete="deleteRow" v-on:row-dragstart="onRowDragstart" v-on:row-dragend="onRowDragend" v-on:row-drop="onRowDrop" />\
          </transition-group>\
        </div>\
        <div class="ctl">\
          <button class="add" tabindex="-1" v-on:click.prevent="addRow">行の追加</button>\
        </div>\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    row: listLinkRowComponent_for_kmu,
    separator: separatorComponent,
    listSeparator: listSeparatorComponent
  },
  data: function(){
    return {
      droppingRow: null
    };
  },
  mixins: [itemMixin],
  methods: {
    // 行の追加
    addRow: function(){
      var rand = getRandomString();
      console.log(rand);
      this.item.rows.push({ component: 'row', text: null, url: null, id: rand});
      console.log(this.item.rows)
    },
    // 行の削除
    deleteRow: function(del_row){
      if( del_row.text && !confirm('行を削除します。よろしいですか？') ) return false;

      var pos = this.item.rows.indexOf(del_row);
      if( pos === -1 ) return false;

      if( this.item.rows.length <= 1 ){ // 残り1行の場合
        this.item.rows[0].text = null;
      }else{
        this.item.rows.splice(pos,1); // 削除対象の行を削除
      }

    },

    // 行のドラッグスタート（並び替え）
    onRowDragstart: function(row){
      this.droppingRow = row; // ドラッグ中の行を記録
    },

    // 要素のドラッグエンド
    onRowDragend: function(row){
      this.droppingRow = null;  // ドラッグ中の行をリセット
    },

    // 行のドロップ
    onRowDrop: function(row){
      if( !this.droppingRow ) return false;

      var dest_pos = row ? this.item.rows.indexOf(row)+1 : 0;
      var src_pos = this.item.rows.indexOf(this.droppingRow);
      this.droppingRow = null; // ドラッグ中の行をリセット

      // 移動先が今から変更がなければスキップ
      if( dest_pos===src_pos || dest_pos===src_pos+1 ) return false;

      // 要素を入れ替え
      var move_row = this.item.rows.splice( src_pos, 1 );
      dest_pos = row ? this.item.rows.indexOf(row)+1 : 0;
      this.item.rows.splice(dest_pos, 0, move_row[0]);

    }
  
  }
};

    // youtubeコンポーネント ///////////////////////////////////////////////////
var youtubeComponent = {
  template: '\
  <div class="item-wrap">\
    <div class="item text youtube">\
      <div class="col1">\
        <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">{{labelName}}</label>\
      </div>\
      <div class="col2">\
        <div class="class-wrap" v-if="isCssClassEnabled">\
          <label :for="item.id+\'_css\'">CSSクラス</label>\
          <input type="text" v-model="item.className" :id="item.id+\'_css\'" />\
        </div>\
        <input type="text" v-model="item.text" v-on:input="onInput" ref="inputElement" />\
      </div>\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  components: {
    separator: separatorComponent
  },
  mixins: [itemMixin],
  mounted: function(){
    // テキストエリアの高さ調整のためにinputイベントを強制発火
    // jqueryではなぜかinputイベントが発火しないため
    // 生のdomで実装する
    var elm = this.$refs.inputElement;
    if( document.createEvent ){
      // IE以外
      var evt = document.createEvent('HTMLEvents');
      evt.initEvent('input', true, true ); // event type, bubbling, cancelable
      elm.dispatchEvent(evt);
    }else{
      // IE
      var evt = document.createEventObject();
      elm.fireEvent('oninput', evt);
    }
  },
  computed: {
    labelName: function(){
      return editor.options.typeNames['youtube'];
    }
  },
  methods: {
    onInput: function(ev){
      // 入力値に応じてリサイズする

      var def_style = window.getComputedStyle(ev.target);
      var line_height = def_style.lineHeight.split('px')[0];
      var padding = def_style.paddingTop.split('px')[0] + def_style.paddingBottom.split('px')[0];

      if( ev.target.scrollHeight > ev.target.offsetHeight ){
        ev.target.style.height = ev.target.scrollHeight+3 + 'px';
      }else{
          
          var height;
          while( true ){
            height = ev.target.style.height.split('px')[0];

            ev.target.style.height = height - line_height +'px';
            if( ev.target.scrollHeight > ev.target.offsetHeight ){
              ev.target.style.height = ev.target.scrollHeight+3 + 'px';
              // _d(ev.target.scrollHeight + padding_top + padding_bottom);
              break;
            }
            break;
          }
      }
      // _d(ev.target.scrollHeight);
      // _d(ev.target.offsetHeight);

    }
  }
};

    // 画像コンポーネント ///////////////////////////////////////////////////
var VSImageComponent = {
  template: '\
  <div class="item-wrap">\
  <div class="item text" style="max-width:9%;float:left;"><div class="col1">\
    <label v-on:dragstart="onLabelDragstart" v-on:dragend="onLabelDragend" draggable="true">画像</label>\
  </div></div>\
    <div class="item image" style="width:38%;float:left;" v-bind:class="{\'ve-unload\': !item.veInited, nofile: !item.image1, dragover:dragover.image1,  dragging:dragging, focus:focus}" v-on:dragover.prevent.stop="onDragover($event,\'image1\')" v-on:dragleave="onDragleave($event,\'image1\')" v-on:drop.prevent.stop="onDrop($event,\'image1\')">\
      <div class="loading" v-if="isLoadingFile.image1"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
      <div class="col2"  style="width:100%">\
        <div class="dnd" v-if="isDndSupported" v-on:dblclick="selectFile(\'image1\')">\
          <div class="msg">画像をドラッグ＆ドロップ<br>（最大ファイルサイズ:{{this.maxFileSize}}）</div>\
        </div>\
          <div class="img" v-bind:class="item.align">\
            <img v-bind:src="item.image1" v-on:dblclick="selectFile(\'image1\')" draggable="false" style="max-width:100%"/>\
          </div>\
        <div class="alt-wrap" v-if="isAltEnabled">\
          <input type="text" v-model="item.image1_alt" :id="item.image1_alt" style="width:100% !important" placeholder="altを入力してください" />\
        </div>\
        <button class="remove" tabindex="-1" v-on:click.prevent="onFileDelete(\'image1\')">画像をクリア</button>\
        <button class="uploader" tabindex="-1" v-on:click.prevent="selectFile(\'image1\')">アップロード済みの画像から選択</button>\
      </div>\
    </div>\
    <div class="item image"  style="width:38%;float:left;" v-bind:class="{\'ve-unload\': !item.veInited, nofile: !item.image2, dragover:dragover.image2, dragging:dragging, focus:focus}" v-on:dragover.prevent.stop="onDragover($event,\'image2\')" v-on:dragleave="onDragleave($event,\'image2\')" v-on:drop.prevent.stop="onDrop($event,\'image2\')">\
      <div class="loading" v-if="isLoadingFile.image2"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
      <div class="col2"  style="width:100%">\
        <div class="dnd" v-if="isDndSupported" v-on:dblclick="selectFile(\'image2\')">\
          <div class="msg">画像をドラッグ＆ドロップ<br>（最大ファイルサイズ:{{this.maxFileSize}}）</div>\
        </div>\
          <div class="img" v-bind:class="item.align">\
            <img v-bind:src="item.image2" v-on:dblclick="selectFile(\'image2\')" draggable="false" style="max-width:100%"/>\
          </div>\
        <div class="alt-wrap" v-if="isAltEnabled">\
          <input type="text" v-model="item.image2_alt" :id="item.image2_alt" style="width:100% !important" placeholder="altを入力してください" />\
        </div>\
        <button class="remove" tabindex="-1" v-on:click.prevent="onFileDelete(\'image2\')">画像をクリア</button>\
        <button class="uploader" tabindex="-1" v-on:click.prevent="selectFile(\'image2\')">アップロード済みの画像から選択</button>\
      </div>\
    </div>\
    <div class="item text">\
      <div class="col3"><button v-on:click.prevent="onItemDelete" tabindex="-1"><span>削除</span></button></div>\
    </div>\
    <separator style="clear:both;" v-on:label-drop-on-separator="onLabelDrop" v-on:separator-click="onSeparatorClick" />\
  </div>',
  props: ['item'],
  data: function(){
    return {
      randomID: [],
      dragover: {
        'image1':false,
        'image2':false
      },
      isDndSupported: editor.options.uploaderDomain ? false : true,
      isLoadingFile: {
        'image1':false,
        'image2':false
      },
      uploadProgress: 0,
      inputComponent: editor.options.inlineVe ? 'visualeditor' : 'plaintexteditor',
      focus: false,
      maxFileSize: editor.options.maxFileSize
    }
  },
  components: {
    visualeditor: VEComponent,
    plaintexteditor: plainTextInputComponent,
    separator: separatorComponent
  },
  mixins: [itemMixin],
  created: function(){
  },
  mounted: function(){
    for(var i=0; i<3; i++) this.randomID.push('image-align-'+getRandomString(6,'numeric'));
  },
  computed: {
    // ALTの入力を許可するか
    isAltEnabled: function () {
      return editor.options.allowImageAlt;
    }
  },
  methods: {
    // ドラッグ&ドロップ処理が可能か
    canDrop: function(){
      return this.isDndSupported && !droppingItem; // 要素のドラッグ&ドロップ処理でなければドロップ処理可
    },

    // アップローダボタンクリック
    selectFile: function(field){
      this.current = field

      // アップローダの表示
      ArticleEditor.showUploader(
        editor.options.uploaderDomain,
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        this.setImage
      );
      return false;
    },


    // アップロードファイル指定
    setImage: function(path){
      var acceptable = editor.options.imageTypes;
      if( path && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !path.match( reg ) ){
          alert('画像の種類は ' + acceptable.join('・') + ' のいずれかである必要があります');
          return false;
        }
      }
      this.$set(this.item, this.current, path)
      this.current = undefined
    },

    // ファイルの削除
    onFileDelete: function(field){
      if( !confirm('画像をクリアします。よろしいですか？\n(アップロードされたファイルは削除されません)') ) return false;
      this.current = field
      this.setImage( null );
    },

    // VEの初期化完了
    onVEInit: function(){
      this.item.veInited = true;
    },

    // ファイルのドラッグオーバー
    onDragover: function(ev, field){
      if( !this.canDrop() ) return false;

      ev.dataTransfer.dropEffect = 'copy';
      // this.dragover = true;
      this.$set(this.dragover, field,  true)
    },

    // ファイルのドラッグリーブ
    onDragleave: function(ev,field){
      if( !this.canDrop() ) return false;

      // this.dragover = false;
      this.$set(this.dragover, field,  false)
    },

    // ファイルのドロップ
    onDrop: function(ev, field){
      if( !this.canDrop() ) return false;

      this.current = field

      var self = this;
      // this.dragover = false;
      this.$set(this.dragover, field, false)
      this.$set(this.isLoadingFile, field,  true)

      this.uploadProgress = 0;

      // ドロップされたファイルのチェック
      var files = ev.dataTransfer.files;
      file_count = files.length;

      var error = '';
      if( file_count == 0 ) error = '画像が指定されていません';
      else if( file_count > 1 ) error = 'アップロードできる画像は1個までです';

      var file = files[0];
      if( !error && file.size > sizeToByte(editor.options.maxFileSize) ) error = '画像のサイズが上限('+editor.options.maxFileSize+')を超過しています';

      var acceptable = editor.options.imageTypes;
      if( !error && acceptable ){
        var reg = new RegExp('\.('+ acceptable.join('|') +')$');
        if( !file.name.match( reg ) ){
          error = '画像の種類は ' + acceptable.join('・') + ' のいずれかである必要があります';
        }
      }
      if( error ){
        this.$set(this.isLoadingFile, field,  false)
        alert( error );
        return false;
      }

      // ファイル送信成功
      var onSuccess = function(file){
        self.$set(self.isLoadingFile, field,  false)
        self.setImage(file); // 画像のセット
      };
      // ファイル送信失敗
      var onError = function(err){
        self.$set(self.isLoadingFile, field,  false)
        alert(err); // エラーを表示
      };
      var progress = function(progress){
        self.uploadProgress = progress*100;
      };

      // ファイルの送信処理
      ArticleEditor.upload.call(
        this,
        file,
        editor.options.uploaderPath,
        editor.options.uploaderDir,
        onSuccess,
        onError,
        progress
      );

    },

    onFocus: function(){
      this.focus = true;
    },

    onBlur: function(){
      this.focus = false;
    }
  }
};


    var defaultItemDef = {
      'big-heading': {type:'big-heading' ,component:'textItem',id:null,  text:null},
      'mid-heading': {type: 'mid-heading', component: 'textItem',id: null,text:null},
      'small-heading': {type: 'small-heading', component: 'textItem', id: null,text:null},
      'text': {type: 'text', component: 'textItem', id: null, text:''},
      've-text': {component: 'VETextItem', align: 'left',id:null, text:'', veInited:false},
      'image': {component: 'imageItem', align: 'left', image: null, text: null, id: null, veInited:false},
      'image-kmu': {component: 'imageKmuItem', align: 'left', image: null, text: null, id: null, veInited:false},
      'link': {component: 'linkItem', url: null, text: null, id: null, newWindow:false},
      'link-kmu': {component: 'linkKmuItem', id: null, links: [{url: null, text: null, newWindow:false},{url: null, text: null, newWindow:false}] },
      'list': {component:'listKmuItem', rows:[{component:'row',text:null, id:null}], isLink:false},
      'list-kmu': {component:'listKmuItem', rows:[{component:'row',text:null, id:null}], isLink:true},
      'list-link-kmu': {component:'listLinkKmuItem', rows:[{component:'row',text:null, id:null}], isLink:true},
      'table': {component: 'tableItem', rows: [], id: null},
      'html': {component: 'htmlItem', html:null, id: null},
      'youtube': {component:'youtubeItem',id:null,  text:null},
      'vs-image': {component: 'VSImageItem', align: 'left', image: null, text: null, id: null, veInited:false}
    };

    // POST値を解析して要素を配置する処理 ////////////////////////////////////

    var inputValue = $elm.val();
    var inputName = $elm.attr('name');
    // _d(inputValue);
    // _d(inputName);

    var itemDef = [];

    var $dom = $('<div></div>');
    $dom.html(inputValue);
    $dom.find('.'+editor.options.wholeWrapTagClass).children().each(function(){

      var $elm = $(this);
      var cont = $elm.html();

      var tag = $elm[0].tagName.toUpperCase();

      if( tag===editor.options.bigHeadingTag.toUpperCase() ||
          tag===editor.options.midHeadingTag.toUpperCase() ||
          tag===editor.options.smallHeadingTag.toUpperCase() ||
          (tag===editor.options.textTag.toUpperCase() && !editor.options.inlineVe ) ){
        // テキストの場合

        var type;
        if( editor.options.types.indexOf('big-heading')>=0 && tag===editor.options.bigHeadingTag.toUpperCase() ) type = 'big-heading';           // 大見出し
        else if( editor.options.types.indexOf('mid-heading')>=0 && tag===editor.options.midHeadingTag.toUpperCase() ) type = 'mid-heading';      // 中見だし
        else if( editor.options.types.indexOf('small-heading')>=0 && tag===editor.options.smallHeadingTag.toUpperCase() ) type = 'small-heading';  // 小見出し
        else if( editor.options.types.indexOf('text')>=0 && tag===editor.options.textTag.toUpperCase() ) type = 'text';                   // テキスト

        if( editor.options.types.indexOf(type) === -1 ) return;

        // brタグを改行に置き換え
        cont = BRtagToNewLine( cont );
        // HTML入力を許可しない場合はエスケープ済みのタグを元に戻す
        if( !editor.options.allowHtmlInText ) cont = unescapeHtml( cont );
        var def = cloneHash(defaultItemDef[type]);
        def.id = getRandomString();
        def.text  = cont;

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) def.className = class_name.trim();
        }

        itemDef.push(def);
      }else if( tag===editor.options.textTag.toUpperCase() && editor.options.inlineVe ){
        // テキスト（VEモード）
        if( editor.options.types.indexOf('text') === -1 ) return;

        var def = cloneHash(defaultItemDef['ve-text']);
        def.id = getRandomString();
        def.text = cont;

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) def.className = class_name.trim();
        }

        def.align='left';
        if( $elm.hasClass('right') ) def.align='right';
        if( $elm.hasClass('center') ) def.align='center';

        itemDef.push(def);
      }else if( tag===editor.options.imageTag.toUpperCase() && $elm.hasClass(editor.options.imageTagClass) ){
        // 画像の場合
        if( editor.options.types.indexOf('image') === -1 ) return;

        var $d_img_wrap = $elm.find('.img').eq(0);

        var def = cloneHash(defaultItemDef['image']);

        if( $d_img_wrap.length ){
          // 行揃え設定の抽出
          if( $d_img_wrap.hasClass('img-left') ) def.align='left';
          if( $d_img_wrap.hasClass('img-right') ) def.align='right';
          if( $d_img_wrap.hasClass('img-center') ) def.align='center';

          var $d_img = $d_img_wrap.find('img').eq(0);
          var img=null;
          if( $d_img ){
            // 画像パスの抽出
            def.image = $d_img.attr('src');
          }
          var $d_text_wrap = $elm.find('.text').eq(0);
          var text=null;
          if( $d_text_wrap.length ){
            // 回り込みテキストの抽出
            var text = $d_text_wrap.html();

            // VEモードでなく、HTML手動許可の場合はタグをエスケープする
            if( !editor.options.inlineVe && editor.options.allowHtmlInText ) text = escapeHtml( text );
            text = text.replace(/&lt;br(\s*\/)?&gt;/g, '<br>'); // 改行のみタグに戻す
            // _d(text);

            def.text = text;
          }
        }

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) class_name = class_name.replace(editor.options.imageTagClass, '').trim();
          if (class_name) def.className = class_name;
        }

        // 画像ALTの取得
        if (editor.options.allowImageAlt) {
          var alt = $d_img.attr('alt');
          def.altName = alt ? alt : null;
        }

        def.id = getRandomString();
        itemDef.push(def);
      }else if( 
        tag===editor.options.imageTag.toUpperCase() && 
        $elm.hasClass('imageCol') && 
        $elm.find('.imageCol__image').length > 0
      ){

        if( editor.options.types.indexOf('image-kmu') === -1 ) return;

        var def = cloneHash(defaultItemDef['image-kmu']);
        var $d_img_wrap = $elm.find('.imageCol__image').eq(0);
        var $d_img = $d_img_wrap.find('img').eq(0);
        var img=null;
        if( $d_img ){
          // 画像パスの抽出
          def.image = $d_img.attr('src');
        }
        var $d_text_wrap = $elm.find('.imageCol__text').eq(0);
        var text=null;
        if( $d_text_wrap.length ){
          // 回り込みテキストの抽出
          var text = $d_text_wrap.html();

          // VEモードでなく、HTML手動許可の場合はタグをエスケープする
          if( !editor.options.inlineVe && editor.options.allowHtmlInText ) text = escapeHtml( text );
          text = text.replace(/&lt;br(\s*\/)?&gt;/g, '<br>'); // 改行のみタグに戻す
          // _d(text);

          def.text = text;
        }

        def.heading = $elm.find('.imageCol__title:eq(0)').text();
        def.link_url = $elm.find('.imageCol__button a:eq(0)').attr('href');
        def.link_text = $elm.find('.imageCol__button a:eq(0)').text();

        def.id = getRandomString();
        itemDef.push(def);

      }else if( tag===editor.options.linkTag.toUpperCase() && $elm.hasClass(editor.options.linkTagClass) ){
        // リンク
        if( editor.options.types.indexOf('link') === -1 ) return;

        var def = cloneHash(defaultItemDef['link']);

        def.url = $elm.find('a').eq(0).attr('href');
        def.text = $elm.find('a').eq(0).text();
        def.newWindow = $elm.find('a').eq(0).attr('target')==='_blank' ? true : false;
        def.id = getRandomString();

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) class_name = class_name.replace(editor.options.linkTagClass, '').trim();
          if (class_name) def.className = class_name;
        }

        itemDef.push(def);
      }else if( tag===editor.options.linkTag.toUpperCase() && $elm.hasClass("buttonCol") ){
        // リンク
        if( editor.options.types.indexOf('link-kmu') === -1 ) return;

        var def = cloneHash(defaultItemDef['link-kmu']);
        $elm.find('a').each(function(i) {
            def.links[i].url = $(this).attr('href');
            def.links[i].text = $(this).text();
            def.links[i].newWindow = $(this).attr('target')==='_blank' ? true : false;
        });
        // def.url = $elm.find('a').eq(0).attr('href');
        // def.text = $elm.find('a').eq(0).text();
        // def.newWindow = $elm.find('a').eq(0).attr('target')==='_blank' ? true : false;
        def.id = getRandomString();

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) class_name = class_name.replace(editor.options.linkTagClass, '').trim();
          if (class_name) def.className = class_name;
        }

        itemDef.push(def);
      }else if( tag===editor.options.listTag.toUpperCase() && $elm.hasClass('listTypeA')){
        // リスト
        if( editor.options.types.indexOf('list-kmu') === -1 ) return;

        var def = cloneHash(defaultItemDef['list-kmu']);

        var rows = [];
        $elm.find('li').each(function(){
          $e = $(this);
          var text = $e.find('span:eq(0)').text()
          var url = '';
          if($e.find('a').length > 0)
            url = $e.find('a').attr('href');
          rows.push({ component: 'row', text: text, url: url, id: getRandomString() });
        });
        if( rows.length===0 ){
          // 入力がない場合は、初期入力用に行を一つだけ追加
          def.rows[0].id = getRandomString();
          rows = def.rows;
        }
        def.rows = rows;

        def.id = getRandomString();

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) def.className = class_name.trim();
        }

        itemDef.push(def);
      }else if( tag===editor.options.listTag.toUpperCase() && $elm.hasClass('arrowLinkList')){
        // リスト
        if( editor.options.types.indexOf('list-link-kmu') === -1 ) return;

        var def = cloneHash(defaultItemDef['list-link-kmu']);

        var rows = [];
        $elm.find('li').each(function(){
          $e = $(this);
          var text = $e.find('span:eq(0)').text()
          var url = '';
          if($e.find('a').length > 0)
            url = $e.find('a').attr('href');
          rows.push({ component: 'row', text: text, url: url, id: getRandomString() });
        });
        if( rows.length===0 ){
          // 入力がない場合は、初期入力用に行を一つだけ追加
          def.rows[0].id = getRandomString();
          rows = def.rows;
        }
        def.rows = rows;

        def.id = getRandomString();

        itemDef.push(def);
      }else if( tag===editor.options.listTag.toUpperCase() ){
        // リスト
        if( editor.options.types.indexOf('list') === -1 ) return;

        var def = cloneHash(defaultItemDef['list']);

        var rows = [];
        $elm.find('li').each(function(){
          $e = $(this);
          var text;
          if( editor.options.allowHtmlInText ) text = $e.html();
          else text = $e.text();
          rows.push({ component: 'row', text: text, id: getRandomString() });
        });
        if( rows.length===0 ){
          // 入力がない場合は、初期入力用に行を一つだけ追加
          def.rows[0].id = getRandomString();
          rows = def.rows;
        }
        def.rows = rows;

        def.id = getRandomString();

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) def.className = class_name.trim();
        }

        itemDef.push(def);
      }else if( tag===editor.options.tableTag.toUpperCase() ){
        // テーブル
        if( editor.options.types.indexOf('table') === -1 ) return;

        var def = cloneHash(defaultItemDef['table']);
        var rows = [];
        $elm.find('tr').each(function(){
          var row = {cols:[]};
          $(this).find('th,td').each(function(){
            var text = $(this).html();
            // VEモードでなく、HTML手動許可の場合はタグをエスケープする
            if( !editor.options.inlineVe && editor.options.allowHtmlInText ) text = escapeHtml(text);
            text = text.replace(/&lt;br(\s*\/)?&gt;/g, '<br>'); // 改行のみタグに戻す

            var col = {text:text};
            if($(this)[0].tagName.toUpperCase()=='TH') col['header'] = true;
            else col['header'] = false;
            row.cols.push(col);
          });
          rows.push(row);
        });
        def.rows = rows;
        def.id = getRandomString();

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) def.className = class_name.trim();
        }

        itemDef.push(def);
      } else if (tag === editor.options.htmlTag.toUpperCase() && $elm.hasClass(editor.options.htmlTagClass)) {
        // HTML
        if (editor.options.types.indexOf('html') === -1) return;

        var def = cloneHash(defaultItemDef['html']);

        // CSSクラス名の取得
        if (editor.options.allowCssClass) {
          var class_name = $elm.attr('class');
          if (class_name) class_name = class_name.replace(editor.options.htmlTagClass, '').trim();
          if (class_name) def.className = class_name;
        }

        def.html = $elm.html();

        def.id = getRandomString();
        itemDef.push(def);
      }
      else if ($elm.hasClass('movie')) {
        if (editor.options.types.indexOf('youtube') === -1) return;
        var def = cloneHash(defaultItemDef['youtube']);
        var cont = $elm.find('iframe').attr('src');
        def.id = getRandomString();
        def.text  = cont;
        itemDef.push(def);

      }else if( tag===editor.options.imageTag.toUpperCase() && $elm.hasClass('imageCol') ){
        var def = cloneHash(defaultItemDef['vs-image']);

        if( $elm.find('img').length ){
            // 行揃え設定の抽出
            def.image1 = $elm.find('img').eq(0).attr('src');
            def.image2 = $elm.find('img').eq(1).attr('src');
        }

        def.id = getRandomString();
        itemDef.push(def);
      }


    });

    // フォーム送信時処理 ////////////////////////////////////////////////////
    this.getSendData = function(){
      var send = '';
      for( var i=0; i<itemDef.length; i++ ){
        var item = itemDef[i];

        if( item.component === 'textItem' ){
          // テキスト
          var text = item.text;
          var text = escapeUnsafeHtml( text ); // 安全でないタグをエスケープ

          // HTML入力を許可しない場合はタグをエスケープする
          if( !editor.options.allowHtmlInText ) text = escapeHtml( text );
          // 改行をbrに置き換え
          text = newlineToBRtag( text );

          if( !text ) continue;

          var type_tag = editor.options.textTag;
          if( item.type === 'big-heading' ) type_tag = editor.options.bigHeadingTag;            // 大見出し
          else if( item.type === 'mid-heading' ) type_tag = editor.options.midHeadingTag;       // 中見出し
          else if( item.type === 'small-heading' ) type_tag = editor.options.smallHeadingTag;   // 小見出し
          else if( item.type === 'text' ) type_tag = editor.options.textTag;                    // テキスト

          // CSSクラス名の設定
          var class_name = '';
          if (editor.options.allowCssClass && item.className) {
            class_name = ' class="'+ escapeHtml(item.className.trim()) +'"';
          }

          send += '<' + type_tag + class_name + '>' + text + '</'+type_tag+'>\n';
        }else if( item.component === 'VETextItem' ){
          // テキスト（VEモード）
          var text = arrangeEditableHtml( item.text );
          if( !text ) continue;

          // CSSクラス名の設定
          var class_name = '';
          if( item.align !== 'left'){
            class_name = ' class="'+ escapeHtml(item.align) +'"';
          }
          if (editor.options.allowCssClass && item.className) {
            class_name = ' class="' + escapeHtml(item.className.trim()) + '"';
          }

          send += '<' + editor.options.textTag + class_name +'>\n'+ text + '\n</'+ editor.options.textTag +'>\n';
        }else if( item.component === 'imageItem' ){
          // 画像
          if( !item.image ) continue;

          var text = arrangeEditableHtml( item.text );
          if( !editor.options.inlineVe && editor.options.allowHtmlInText ){
            // VEモードでなく、HTMLの手動許可の場合
            // 入力タグを有効にするためにエスケープ解除
            text = unescapeHtml( text );
          }

          // CSSクラス名の設定
          var class_name = '';
          if (editor.options.allowCssClass && item.className) {
            class_name = ' ' + escapeHtml(item.className.trim());
          }

          // 画像ALTの設定
          var alt_name = '';
          if (editor.options.allowImageAlt && item.altName) {
            alt_name = ' alt="' + escapeHtml(item.altName) + '"';
          }

          send += '<' + editor.options.imageTag + ' class="' + editor.options.imageTagClass + class_name + '">\n<p class="img img-'+ item.align +'"><img src="'+ item.image +'" title="'+ item.image + '"' + alt_name + ' /></p>\n<p class="text">'+ text + '</p>\n</'+ editor.options.imageTag +'>\n';
        }else if( item.component === 'imageKmuItem' ){
          // 画像
          if( !item.image ) continue;

          var text = arrangeEditableHtml( item.text );
          if( !editor.options.inlineVe && editor.options.allowHtmlInText ){
           // VEモードでなく、HTMLの手動許可の場合
           // 入力タグを有効にするためにエスケープ解除
           text = unescapeHtml( text );
          }
          send += '<div class="imageCol"><div class="imageCol__image"><img src="'+ item.image +'" alt=""></div>';
          send += '<div class="imageCol__body">';
          if( item.heading ){
            send += '<h3 class="imageCol__title">'+ item.heading +'</h3>';
          }
          if( text ){
            send += '<p class="imageCol__text">'+ text +'</p>';
          }
          if( item.link_url && item.link_text){
            send += '<p class="imageCol__button"><a href="'+ item.link_url +'" class="buttonA">'+ item.link_text +'</a></p>';
          }
          send += '</div></div>\n';
        }else if( item.component === 'linkItem' ){
          // リンク
          if( !item.url ) continue;

          // 入力値をエスケープ
          var url = escapeHtml(item.url);
          var text = escapeHtml(item.text);
          if( !text ) text = url;  // テキストが入力されていなければURLをセットする

          // CSSクラス名の設定
          var class_name = '';
          if (editor.options.allowCssClass && item.className) {
            class_name = ' ' + escapeHtml(item.className.trim());
          }

          send += '<'+ editor.options.linkTag + ' class="'+ editor.options.linkTagClass + class_name + '">\n<a href="'+ url +'"'+ (item.newWindow ? ' target="_blank" rel="noopener"' : '') +'>'+ text +'</a>\n</'+ editor.options.linkTag +'>\n';
        }else if( item.component === 'linkKmuItem' ){

          // リンク
          if( item.links.length === 0) continue;
            
          // CSSクラス名の設定
          var class_name = '';
          if (editor.options.allowCssClass && item.className) {
            class_name = ' ' + escapeHtml(item.className.trim());
          }
          
          send += '<'+ editor.options.linkTag + ' class="buttonCol'+ class_name + '">\n';

          for (let i = 0; i < item.links.length; i++) {
            if( item.links[i].url === null || item.links[i].url === '') continue;
          
            // 入力値をエスケープ
            var url = escapeHtml(item.links[i].url);
            var text = escapeHtml(item.links[i].text);
            if( !text ) text = url;  // テキストが入力されていなければURLをセットする
            send += '<p class="buttonCol__item">\n<a href="'+ url +'"'+ (item.links[i].newWindow ? ' target="_blank" rel="noopener"' : '') +' class="buttonA">'+ text +'</a></p>\n';
          }
          send += '</'+ editor.options.linkTag +'>\n';

        }else if( item.component === 'listItem' ){
          // リスト

          var list_tag = '';
          item.rows.forEach(function(row){
            if( !row.text ) return;
            var text = row.text;

            // HTML入力を許可しない場合はタグをエスケープする
            if( !editor.options.allowHtmlInText ) text = escapeHtml(text);

            list_tag += '<li>' + text + '</li>\n';
          });
          if( list_tag ){
            // CSSクラス名の設定
            var class_name = '';
            if (editor.options.allowCssClass && item.className) {
              class_name = ' class="' + escapeHtml(item.className.trim()) + '"';
            }

            send += '<' + editor.options.listTag + class_name +'>\n' + list_tag + '</'+ editor.options.listTag +'>\n';
          }

        }else if( item.component === 'listKmuItem' ){
          // リスト

          var list_tag = '';
          item.rows.forEach(function(row){
            if( !row.text ) return;
            var text = row.text;

            // HTML入力を許可しない場合はタグをエスケープする
            if( !editor.options.allowHtmlInText ) text = escapeHtml(text);

            list_tag += '<li class="listTypeA__item"><span>' + text + '</span>';
            if( row.url ) {
                list_tag += '<a href="'+row.url+'" class="listTypeA__link">疾患の解説</a>';
            }
            list_tag += '</li>\n';
          });
          if( list_tag ){
            // CSSクラス名の設定
            var class_name = ' class="listTypeA -pcCol2"';
            //if (editor.options.allowCssClass && item.className) {
            //  class_name = ' class="' + escapeHtml(item.className.trim()) + '"';
            //}

            send += '<' + editor.options.listTag + class_name +'>\n' + list_tag + '</'+ editor.options.listTag +'>\n';
          }

        }else if( item.component === 'listLinkKmuItem' ){
          // リスト

          var list_tag = '';
          item.rows.forEach(function(row){
            if( !row.text || !row.url) return;
            var text = row.text;

            // HTML入力を許可しない場合はタグをエスケープする
            if( !editor.options.allowHtmlInText ) text = escapeHtml(text);

            list_tag += '<li class="arrowLinkList__item"><a href="'+row.url+'"><span>'+text+'</span></a></li>\n';
          });
          if( list_tag ){
            // CSSクラス名の設定
            var class_name = ' ';
            send += '<' + editor.options.listTag + ' class="arrowLinkList">\n' + list_tag + '</'+ editor.options.listTag +'>\n';
          }

        }else if( item.component === 'tableItem' ){
          // テーブル

          var table_tag = '';
          var thead_tag = '';
          var tbody_tag = '';

          var row_no = 0;
          item.rows.forEach(function(row){
            var cols = [];
            var header_count = 0; // 見出し列の数をカウント
            var empty_count = 0;  // 見出し列の未入力数をカウント
            var scope = '';
            // 列全体の見出しの数をカウント
            row.cols.forEach(function(col){
              var text = arrangeEditableHtml(col.text);
              if(col.header) header_count++;
              if(!text) empty_count++;
            });
            if(row.cols.length == empty_count) return; // 全て未入力の場合はスキップ

            if(row.cols.length == header_count) scope = 'col'; // 行全体が見出し
            else scope = 'row'; // 列全体が見出し
            row.cols.forEach(function(col){

              var text = arrangeEditableHtml(col.text);
              if( !editor.options.inlineVe && editor.options.allowHtmlInText ){
                // VEモードでなく、HTMLの手動許可の場合
                // 入力タグを有効にするためにエスケープ解除
                text = unescapeHtml( text );
              }

              if(col.header) cols.push('<th scope="'+scope+'">'+text+'</th>'+"\n");
              else cols.push('<td>'+text+'</td>'+"\n");
            });

            var tr_tag = '';
            tr_tag += "<tr>\n";
            tr_tag += cols.join('');
            tr_tag += "</tr>\n";

            if(row_no==0 && scope=='col') thead_tag += tr_tag; // 1行目全体が見出しの場合はtheadを設定
            else tbody_tag += tr_tag;

            row_no++;
          });

          if(thead_tag) table_tag += "<thead>\n" + thead_tag + "</thead>\n";
          if(tbody_tag) table_tag += "<tbody>\n" + tbody_tag + "</tbody>\n";

          if(table_tag){
            // CSSクラス名の設定
            var class_name = '';
            if (editor.options.allowCssClass && item.className) {
              class_name = ' class="' + escapeHtml(item.className.trim()) + '"';
            }

            send += '<' + editor.options.tableTag + class_name +'>\n' + table_tag + '</'+ editor.options.tableTag +'>\n';
          }

        } else if (item.component === 'htmlItem') { // HTML
          var html = item.html;
          var html = escapeUnsafeHtml(html); // 安全でないタグをエスケープ
          if (!html) continue;

          // CSSクラス名の設定
          var class_name = '';
          if (editor.options.allowCssClass && item.className) {
            class_name = ' ' + escapeHtml(item.className.trim());
          }

          send += '<' + editor.options.htmlTag + ' class="' + editor.options.htmlTagClass + class_name + '">' + html + '</' + editor.options.htmlTag + '>\n';
        }
        else if( item.component === 'youtubeItem' ){
          // youtube

          var text = item.text;
          if( !text || !text.startsWith("https://www.youtube.com/") ) continue;

          send += '<div class="movie">';
          send += '<iframe width="560" height="315" src="'+text+'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
          send += '</div>';
        }else if( item.component === 'VSImageItem' ){
          if( !item.image1 && !item.image2 ) continue;
            send += '<div class="imageCol">';
          if( item.image1 ){
            send += '<div><img src="'+ item.image1 +'" alt="'+ (item.image1_alt === undefined ? '' : item.image1_alt) +'"></div>';
          }
          if( item.image2 ){
            send += '<div><img src="'+ item.image2 +'" alt="'+ (item.image2_alt === undefined ? '' : item.image2_alt) +'"></div>';
          }
          send += '</div>';
        }

      }
      if( send ) send = '<'+ editor.options.wholeWrapTag +' class="'+ editor.options.wholeWrapTagClass +'">\n'+ send +'</'+ editor.options.wholeWrapTag +'>\n';

      return send;
    };

    var scripts = [];
    if( typeof Vue === 'undefined' ){ // Vueライブラリのロードがされていない
      scripts.push([SCRIPT_DIR_PATH+'lib/vue/vue.min.js']);
    }
    if( scripts.length ){
      loadScript(scripts,
        function(){ // ライブラリロード完了
          init(); // 初期化処理実行
        },
        function(){ // エラー
          alert('ライブラリの読み込みに失敗しました');
        });
    }else{
      init();
    }

    // 初期化処理 /////////////////////////////////////////////////////////////
    function init(){
      if( editor.vue ) return; // すでに初期化済みなら終了
      editor.vue = new Vue({
        el: element,
        template: '\
        <div class="article-editor" v-on:dragover.prevent.stop="onFileDrag">\
          <separator v-on:label-drop-on-separator="onItemDrop" v-on:separator-click="onSeparatorClick" />\
          <transition name="tips" tag="div">\
          <div class="tips" v-if="!items.length"><div class="separator" v-on:click.prevent=""><button tabindex="-1"><span>挿入</span></button></div>をクリックすると{{tipsLabel}}</div>\
          </transition>\
          <transition-group name="item" tag="div">\
            <component v-bind:is="item.component" v-for="(item, index) in items" :key="item.id" v-bind:item=item v-on:item-delete="deleteItem" v-on:separator-click="onSeparatorClick" v-on:item-dragstart="onItemDragstart" v-on:item-dragend="onItemDragend" v-on:item-drop="onItemDrop" v-on:table-header-menu-click="onTableHeaderMenuClick" />\
          </transition-group>\
          <tableMenu v-bind:data="tableMenu" v-on:menu-click="doTableAction" ref="tableMenu" />\
          <itemMenu v-bind:data="itemMenu" v-on:menu-click="addItem" ref="itemMenu" />\
          <input v-bind:name="inputName" type="hidden" ref="sendData" />\
        </div>',
        data: function(){
          return {
            items: [],
            itemMenu: {
              show: false,
              position: {x:0, y:0},
              size: {width:0, height:0}
            },
            tableMenu: {
              show: false,
              direction: null,
              mode: 'row',
            },
            inputName: null,
            tipsLabel: null,
          }
        },
        components: {
          separator: separatorComponent,
          itemMenu: itemMenuComponent,
          tableMenu: tableMenuComponent,
          textItem: textComponent,
          VETextItem: VETextComponent,
          imageItem: imageComponent,
          imageKmuItem: imageComponent_for_kmu,
          linkItem: linkComponent,
          linkKmuItem: linkComponent_for_kmu,
          listItem: listComponent,
          listKmuItem: listComponent_for_kmu,
          listLinkKmuItem: listLinkComponent_for_kmu,
          tableItem: tableComponent,
          htmlItem: htmlComponent,
          youtubeItem: youtubeComponent,
          VSImageItem: VSImageComponent
        },
        created: function(){
          this.items = itemDef;
          this.inputName = inputName;

          // 初期表示時のTIPSメッセージの設定
          var tips_label = [];
          editor.options.types.forEach(function(type){
            tips_label.push( editor.options.typeNames[type] );
          });
          if( tips_label.length===1 ) this.tipsLabel = 'その位置に' + tips_label[0] + 'が追加されます。';
          else this.tipsLabel = 'その位置に選んだ要素（'+tips_label.join('・') + '）が追加されます。';
        },
        mounted: function(){
          // フォーム送信イベントを設定
          var $form = $(this.$el).closest('form');
          var self = this;
          if( $form ){
            $form.bind('submit', function(ev){
              var send = editor.getSendData();
              $(self.$refs.sendData).val( send );
              // ev.preventDefault();
            });
          }

          if( editor.options.onLoad ){
            // 設定完了コールバックを呼ぶ
            editor.options.onLoad.call(editor);
          }
          if( editor.options.onUpdate ){
            editor.options.onUpdate.call(editor);
          }

        },
        methods: {

          // 要素の追加
          addItem: function(type){

            // テキストの場合、VEモードのON/OFFによってコンポーネントを切り替える
            if( type==='text' && editor.options.inlineVe ) type = 've-text';

            // クリックされた位置を検索し、その位置に要素を追加する
            var pos = currentItem ? this.items.indexOf(currentItem)+1 : 0;
            var item = cloneHash(defaultItemDef[type]);
            if (type === 'list' || type === 'list-kmu' || type === 'list-link-kmu') {
              item.rows[0].id = getRandomString();
            }
            item.id = getRandomString();

            this.items.splice(pos ,0, item);

            currentItem = null;
          },

          // 要素の削除
          deleteItem: function(del_item){
            if( !confirm('要素を削除します。よろしいですか？') ) return false;

            var pos = this.items.indexOf(del_item);
            if( pos !== -1 ){
              this.items.splice(pos,1); // 削除対象の要素を削除
            }
          },

          // 要素のメニュー表示
          // separator: クリックされたセパレータオブジェクト
          onSeparatorClick: function(item,ev){
            currentItem = item; // クリックされた位置を記録
            this.itemMenu.show = false;

            if( editor.options.types.length===1 ){ // 対応する項目がひとつだけの場合
              // メニューを表示せず、即挿入処理を行う
              this.addItem( editor.options.types[0] );
              return;
            }

            this.itemMenu.show = true;
            this.itemMenu.direction = 'below';

            var $menu = $(this.$refs.itemMenu.$el);
            var $editor = $(this.$el);

            // メニューの位置計算
            var x = ev.pageX - $editor.offset().left;
            var y = ev.pageY - $editor.offset().top;
            x -= $menu.outerWidth()/2;

            // X座標の位置調整
            var tmp_x = ev.pageX - $menu.outerWidth()/2;
            if( tmp_x<0 ){ // 画面左端にはみ出す場合
              x = $editor.offset().left*-1; // 見切れないように位置調整
            }
            tmp_x = ev.pageX + $menu.outerWidth()/2;
            if( tmp_x > $(document).width() ){ // 画面右端にはみ出す場合
              x -= tmp_x - $(document).width(); // 見切れないように位置調整
            }

            // Y座標の位置調整
            var tmp_y = ev.pageY + $menu.outerHeight() + 30;
            if( tmp_y > $(document).height() ){ // 画面下端にはみ出す場合
              y -= $menu.outerHeight();
              this.itemMenu.direction = 'above';
            }
            if( this.itemMenu.direction == 'above' ) y -= 15;
            else y += 30;

            $menu.css({left:x+'px', top:y+'px'});
            // _d({x:x,y:y});

            // メニュー外のクリック検知のためのイベント
            $(document).bind('click', this.hideItemMenu);
          },

          // 要素のメニュー非表示
          hideItemMenu: function(){
            this.itemMenu.show = false;
            currentItem = null;

            // メニュー外のクリック検知のためのイベントを削除
            $(document).unbind('click', this.hideItemMenu);
          },

          // テーブルの操作実行
          doTableAction: function(action)
          {
            if( currentTable ){
              currentTable.doAction(action);
            }
            currentTable = null;
          },

          // テーブルコンポーネントのヘッダメニューのクリック
          onTableHeaderMenuClick: function(table,mode,ev){
            $(document).unbind('click', this.hideTableMenu);
            currentTable = table;

            this.tableMenu.show = true;
            this.tableMenu.mode = mode;
            this.tableMenu.direction = 'above';

            var $menu = $(this.$refs.tableMenu.$el);
            var $editor = $(this.$el);

            var self = this;
            setTimeout(function(){
              // メニューの高さを取得する必要があるため
              // 次のDOM更新サイクルまで待機して処理

              _d({pageX:ev.pageX, pageY:ev.pageY, menuH:$menu.height()});

              var x = ev.pageX - $editor.offset().left;
              var y = ev.pageY - $editor.offset().top - $menu.height();
              x -= $menu.outerWidth()/2;

              // _d({x:x,y:y});

              // X座標の位置調整
              var tmp_x = ev.pageX - $menu.outerWidth()/2;
              if( tmp_x<0 ){ // 画面左端にはみ出す場合
                x = $editor.offset().left*-1; // 見切れないように位置調整
              }
              tmp_x = ev.pageX + $menu.outerWidth()/2;
              if( tmp_x > $(document).width() ){ // 画面右端にはみ出す場合
                x -= tmp_x - $(document).width(); // 見切れないように位置調整
              }

              // Y座標の位置調整
              var tmp_y = ev.pageY - $menu.outerHeight();
              if( tmp_y < 0 ){ // 画面上端にはみ出す場合
                y += $menu.outerHeight();
                self.tableMenu.direction = 'below';
              }

              // 吹き出しの位置調整
              if( self.tableMenu.direction == 'above' ) y -= 25;
              else y += 20;

              $menu.css({left: x+'px', top: y+'px'});
              self.tableMenu.show = true;

              // メニュー外のクリック検知のためのイベント
              $(document).bind('click', self.hideTableMenu);
            }, 0);
          },

           // テーブルメニュー非表示
          hideTableMenu: function(){
            this.tableMenu.show = false;
            // メニュー外のクリック検知のためのイベントを削除
            $(document).unbind('click', this.hideTableMenu);
            if(currentTable) currentTable.doAction(null);
            currentTable = null;
          },

          onFileDrag: function(ev){
            ev.dataTransfer.dropEffect = 'none'; // ドラッグ禁止
          },

          // 要素のドラッグスタート（並び替え）
          onItemDragstart: function(item){
            droppingItem = item; // ドラッグ中の要素を記録
          },

          // 要素のドラッグエンド
          onItemDragend: function(item){
            droppingItem = null;  // ドラッグ中の要素をリセット
            // _d(item);
          },

          // 要素のドロップ
          onItemDrop: function(dest_item){
            if( !droppingItem ) return false;

            var dest_pos = dest_item ? this.items.indexOf(dest_item)+1 : 0;
            var src_pos = this.items.indexOf(droppingItem);
            droppingItem = null; // ドラッグ中の要素をリセット

            // _d('dest_pos:'+dest_pos + ' src_pos:'+src_pos);
            // 移動先が今から変更がなければスキップ
            if( dest_pos===src_pos || dest_pos===src_pos+1 ) return false;

            // 要素を入れ替え
            var move_item = this.items.splice(src_pos, 1);
            dest_pos = dest_item ? this.items.indexOf(dest_item)+1 : 0;
            this.items.splice(dest_pos, 0, move_item[0]);

          }

        }
      });

      // 要素の変更検知・コールバック実行
      if( editor.options.onUpdate ){
        var timer=null;
        editor.vue.$watch('items', function(){
          if( timer ) clearTimeout(timer);
          timer = setTimeout(function(){
            editor.options.onUpdate.call(editor);
          }, editor.options.updateInterval);
        }, {deep: true});
      }

    }

  }

  // アップローダモーダルの表示 /////////////////////////////////////////////////////
  var $currOverlay = null;
  ArticleEditor.showUploader = function( domain, path, dir, callback ){
    // console.log('showUploader');
    if( $currOverlay ) return false; // アップローダが既に表示されている

    // デフォルトで表示するディレクトリを指定
    domain = domain ? domain : '';
    var uploader_path = path;
    if( domain ) uploader_path = domain + uploader_path;
    if( dir ) uploader_path += '#' + dir;

    $currOverlay = $('<div class="article-editor-overlay"><div class="uploader"><button class="close"><i class="fa fa-times" aria-hidden="true"></i></button><iframe src="'+uploader_path+'"></iframe></div></div>');

    // アップローダーのポップアップウィンドウを作成・表示
    $currOverlay
      .addClass('active')
      .bind('click', ArticleEditor.hideUploader);

    $('body').append($currOverlay);

    // Uploaderからのデータ受け取り(通常時)
    if( !window.selectUploadFile ){
      window.selectUploadFile = function( file_path ){ // ファイルが選択された
        if( callback ) callback(domain+file_path);
        ArticleEditor.hideUploader(); // アップローダを非表示に
      };
    }

    // Uploaderからのデータ受け取り(クロスドメイン時)
    if( !window.onmessage ){
      window.onmessage = function(e){
        try{
          var d = JSON.parse(e.data);
          if( typeof d.path !== 'undefined' ){
            if( callback ) callback(domain+d.path);
          }
        }catch(e){
        }
        ArticleEditor.hideUploader();
      }
    }

  }

  // アップローダモーダル非表示 ////////////////////////////////////////////////////
  ArticleEditor.hideUploader = function(){
    if( !$currOverlay ) return false; // アップローダが表示されていない
    $currOverlay.removeClass('active');

    window.selectUploadFile = null;
    window.onmessage = null;

    // アニメーション速度を考慮し、一定時間後に要素を削除
    setTimeout(function(){
      $currOverlay.remove();
      $currOverlay = null;
    }, 500);
  };

  // アップロード処理 ///////////////////////////////////////////////////////////
  ArticleEditor.upload = function( file, path, dir, callback, err_callback, prg_callback ){

    // フォームデータ作成
    var fd = new FormData();
    fd.append( 'dir', dir );
    fd.append( 'action', 'upload' );
    fd.append( 'upload', file );

    var xhr = new XMLHttpRequest();
    xhr.open( 'POST', path );
    xhr.setRequestHeader( 'X-REQUESTED-WITH', 'XMLHttpRequest' );
    xhr.onreadystatechange = function(ev){
      // _d('status:'+xhr.readyState + ' status:'+xhr.status);
      if( xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200 ){
        try{
          var res = JSON.parse( xhr.response );
          if( res.errors ){// エラーアリ
            if( err_callback ) err_callback( res.errors );
            return false;
          }
          if( callback ) callback( res.result.files[0].web_path );
        }catch(e){
          if( err_callback ) err_callback('不正なデータを受信しました');
        }
      }
    }
    xhr.upload.onprogress = function(ev){
      if( prg_callback ) prg_callback(ev.loaded/ev.total);
    }
    xhr.send( fd );

  };

  // ユーティリティライブラリ ///////////////////////////////////////////////
function getRandomString(len, type) {
  if (typeof len === 'undefined') len = 8;
  if (typeof type === 'undefined') type = 'alphanumeric';

  var chars;
  if (type === 'alphanumeric') chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  else if (type === 'numeric') chars = '0123456789';

  var clen = chars.length;
  var r = "";
  for (var i = 0; i < len; i++) {
    r += chars[Math.floor(Math.random() * clen)];
  }
  return r;
}

// サイズ表記からバイト数への変換
function sizeToByte( size ){
  var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
  var size = size.toUpperCase();
  var reg_unit = units.join('|');
  var reg = new RegExp( '^(([1-9]\\d*|0)(\\.\\d+)?)\\s*('+ reg_unit + '?)$' );

  if( size.match(reg) ){
    var size = RegExp.$1;
    var unit = RegExp.$4;

    var pos = units.indexOf( unit );
    size = Math.floor( size * Math.pow(1024,pos) );
    return size;
  }
  return null;
};

// HTMLのエスケープ
function escapeHtml( str ){
  if( !str ) return str;
  return str.replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace('/ /', '&nbsp;');
};

// 安全でないタグのエスケープ
function escapeUnsafeHtml( str ){
  if( !str ) return str;
  return str
      .replace(/<script(\s+.+?)?>(.+?)<\/script(\s+.?)?>/g, '')
      .replace(/<div(\s+.+?)?>(.+?)<\/div(\s+.?)?>/g, '');
}

// HTMLのエスケープ解除
function unescapeHtml( str ){
  if( !str ) return str;
  return str.replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'")
        .replace(/&nbsp;/g, '');
}

// contenteditableで入力されたHTMLを
// ブラウザの差違を取り除いて整理する
function arrangeEditableHtml( html ){
  if( !html ) return html;
  html = html.replace(/(<div.*?>|<p.*?>)/ig, '<br>');
  html = html.replace(/(<\/div\s*>|<\/p\s*>)/ig, '');
  html = html.replace(/^<br(\s+[^>]+)?(\s*\/)?>/i, ''); // 先頭のbr削除
  return html;
}

// 改行を<br>に置き換える
function newlineToBRtag( val ){
  if( !val ) return val;
  return val.replace(/(\r\n|\r|\n)/g, '<br>'); 
}

// <br>を改行に置き換える
function BRtagToNewLine( tag ){
  if( !tag ) return tag;
  return tag.replace(/<br(\s*\/)?>/g, "\n"); 
}

// ブラウザ情報を取得する 
function getBrowser(){
  var ua = window.navigator.userAgent.toLowerCase();
  if( ua.indexOf('edge') !== -1 ) return 'edge';
  else if( ua.indexOf("iemobile") !== -1 ) return 'iemobile';                  // ieMobile
  else if( ua.indexOf('trident/7') !== -1 ) return 'ie';                    // ie11
  else if( ua.indexOf('msie') !== -1 && ua.indexOf('opera') === -1 ) return 'ie';       // ie6〜10
  else if( ua.indexOf('chrome')  !== -1 && ua.indexOf('edge') === -1 )   return 'chrome';    // Chrome
  else if( ua.indexOf('safari')  !== -1 && ua.indexOf('chrome') === -1 ) return 'safari';    // Safari
  else if( ua.indexOf('opera')   !== -1 ) return 'opera';                    // Opera
  else if( ua.indexOf('firefox') !== -1 ) return 'firefox';                  // Firefox
  else return null;
}

// IEバージョンの取得
function getIEVersion(){
  var ua = window.navigator.userAgent.toLowerCase();
  var ver = window.navigator.appVersion.toLowerCase();

  if( ua.indexOf('trident/7') !== -1 ) return 11;
  if( ua.indexOf("msie") !== -1 && ua.indexOf('opera') === -1 ){
    if( ver.indexOf("msie 6.")  !== -1 ) return 6;
    else if( ver.indexOf('msie 7.')  !== -1 ) return 7;
    else if( ver.indexOf('msie 8.')  !== -1 ) return 8;
    else if( ver.indexOf('msie 9.')  !== -1 ) return 9;
    else if( ver.indexOf('msie 10.') !== -1 ) return 10;
  }
  return null;
}

// ランダム文字列の取得
function getRandomString( len, type ){
  if( typeof len === 'undefined' ) len = 8;
  if( typeof type === 'undefined' ) type = 'alphanumeric';

  var chars;
  if( type === 'alphanumeric' ) chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  else if( type === 'numeric' ) chars = '0123456789';

  var clen = chars.length;
  var r = "";
  for( var i=0; i<len; i++ ){
    r += chars[Math.floor(Math.random()*clen)];
  }
  return r;
}

// 現在実行されているスクリプトのパス（ディレクトリ）の取得
function getScriptPath(){
  var script;
  if( document.currentScript ){
    script = document.currentScript.src;
  }else{
    var scripts = document.getElementsByTagName('script');
    script = scripts[scripts.length-1];
    script = script.src;
  }
  if( script && script.match(/^(.+?)([^\/]+?)$/ ) ){
    return RegExp.$1;
  }
};

// CSSを動的に読み込む
function loadCSS( path ){
  var head = document.getElementsByTagName('head').item(0);
  var link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = path;
  link.type = 'text/css';

  head.appendChild(link);
}
// Scriptを動的に読み込む
var LOADING_SCRIPTS = [];
function loadScript( path, callback, err_callback ){

  if( typeof path==='string' ) path = [path];

  var target_scripts = [];
  path.forEach(function(p){
    if( LOADING_SCRIPTS.indexOf( path ) === -1 ){
      // 読み込もうとしているスクリプトがすでに処理中でなければ
      // 読み込み対象としてマーク
      target_scripts.push(p);
    }
  });

  if( target_scripts.length===0 ){
    if( callback ) callback();
    return;
  }

  // タイムアウト監視タイマー
  var load_timer = setTimeout(function(){
    if( err_callback ) err_callback(); // エラーコールバック実行
  }, 5000);

  // スクリプトの動的ロード処理
  var loaded_count = 0;
  target_scripts.forEach(function(p){
    var head = document.getElementsByTagName('head').item(0);
    var script = document.createElement('script');
    script.src = p;
    head.appendChild(script);

    LOADING_SCRIPTS.push(p);
    script.onload = function(){
      loaded_count++;
      if( loaded_count === target_scripts.length ){
        // 全てのスクリプトが読み込み完了
        clearTimeout( load_timer ); // タイムアウトのタイマークリア
        if( callback ) callback( path ); // コールバック実行
      }

      // ローディング中のスクリプトの記録を除去
      var index = LOADING_SCRIPTS.indexOf( p );
      if( index !== -1 ) LOADING_SCRIPTS.splice( index, 1 );

      script.onload = null;
      if( head && script.parentNode ) head.removeChild( script );
    };

  });
}


// 連想配列のコピー
function cloneHash( hash ){

  return JSON.parse(JSON.stringify(hash));
  // var ret = {};
  // for( var k in hash ){
  //   if( hash[k] instanceof Array ){
  //     ret[k] = hash[k].concat();
  //   }else if( hash[k] instanceof Object ){
  //     ret[k] = {};
  //     for( var k2 in hash[k] ){
  //       ret[k][k2] = hash[k][k2];
  //     }
  //   }else{
  //     ret[k] = hash[k];
  //   }

  // }
  // return ret;
}

// デバッグ出力
function _d( msg ){
  if( !DEBUG ) return false;
  if( typeof console === "undefined" || typeof console.log === "undefined" ){
    console = {};
  }else{
    console.log( msg );
  }
}

// キャメルケース(testProperty)からケバブケース(test-property)への変換
function kebabCase(camel) {
  return camel.replace(/[A-Z]/g, function(s){
    return '-' + s.charAt(0).toLowerCase();
  });
}

  return function(selector, options){
    if( !SUPPORTED ){
      alert('[ArticleEditor] お使いのブラウザはサポートされていません');
      return null;
    };

    var self = this;
    var init = function(){
      // selectorにマッチした要素を全て取得し、
      // AttachUploadを適用する
      self.instances = [];

      if (selector instanceof HTMLElement) {
        var editor = new ArticleEditor(selector, options);
        self.instances.push(editor);
      } else {
        $(selector).each(function () {
          var editor = new ArticleEditor($(this)[0], options);
          self.instances.push(editor);
        });
      }
    }

    if( typeof jQuery === 'undefined' ){ // jQueryがロードされていない
      loadScript([SCRIPT_DIR_PATH+'lib/jquery/jquery.min.js'],function(){
        init();
      });
    }else{
      init();
    }

  };

})();

