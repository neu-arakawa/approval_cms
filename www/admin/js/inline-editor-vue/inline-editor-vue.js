/*
 * InlineEditor
 * ver. 1.02
 * 
 * require IE >= 11
 *
 */

var InlineEditor = (function(){
  
  var DEBUG = true;
  var SCRIPT_DIR_PATH = getScriptPath();

  var FILE_API_ENABLED = window.File && window.FileReader && window.FileList && window.Blob;
  var BROWSER = getBrowser();
  var SUPPORTED = ( BROWSER=='edge' || BROWSER=='chrome' || BROWSER=='safari' || BROWSER=='firefox' ||
          (BROWSER=='ie' && getIEVersion()>=10 ) ) && FILE_API_ENABLED;

  // CSSを動的にロード
  loadCSS(SCRIPT_DIR_PATH + 'css/style.css');
  loadCSS(SCRIPT_DIR_PATH + 'css/ve.css');

  var InlineEditor = function(element, options){

    var elm = DOM( element );
    var form = elm.closest('form');
    if( !form ){
      alert('[InlineEditor] 指定の要素:('+selector+') がフォーム要素に含まれないため、正常に動作しません');
      return null;
    }

    var editor = this;

    // デフォルトのオプション設定　//////////////////////////////////////////
    var defaultOptions = {
      // 'veInlineClasses': {'bold':'太字', 'highlight':'ハイライト', 'annotation':'注釈', 'link':'リンク' }, // キーにはクラス名を、値にはメニューに表示されるタイトルを設定。linkの場合は例外的にクラスではなく、リンクの挿入に対応する場合に設定
      'veStyleFormats': [],
      'placeholder': null,
      'onLoad': null,            // 要素のセットアップが完了して使用可能になったときのコールバック
      'uploaderPath': '/uploader',      // Uploaderプラグインのパス(Web)
      'uploaderDir': '/',               // Uploaderプラグインのアップロードディレクトリ名(/始まり)
    };

    // 設定値のセットアップ ////////////////////////////////////////////////
    this.options = cloneHash( defaultOptions );
    var droppingItem = null; // ドラッグ＆ドロップ操作中の要素
    var currentItem = null;; // 挿入位置判定のためのセパレータ

    for( var k in options ){
      this.options[k] = options[k];
    }

    // 要素ごとに設定されたdata-*属性からオプションの上書きを行う
    for( var k in defaultOptions ){
      var tag_prop = 'data-'+ kebabCase(k);
      var tag_val = elm.attr(tag_prop);
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
    // ビジュアルエディタコンポーネント ///////////////////////////////////////////////////
var VEComponent = {
  // blurイベントを設定するのは、VE経由でcontenteditableを操作する場合、inputイベントが発火しないため
  template: '<div class="ve-field" v-bind:class="{inited: veInited, placeholded: placeholded}" contenteditable="false" v-on:input="onTextChange" v-on:focus="onFocus" v-on:blur="onBlur" ref="inputElement"></div>',
  props: ['value'],
  data: function(){
    return {
      placeholded: false,
      veInited: false
    };
  },
  created: function(){

  },
  mounted: function(){
    // データをcontentEditableの領域に反映させる
    var dom = DOM(this.$refs.inputElement);
    if( this.value ) dom.html(this.value);
    else dom.html('<br>'); // 入力が全くないとキャレットが消失するのを回避

    this.loadTinyMCE(); // TinyMCEのロード

    // 初期値を親に伝えるため、イベントを強制発火
    dom.trigger('blur');
  },
  methods: {
    init: function(){
      var self = this;

      //var toolbar = [];
      //var styles = [];
      //for( var cls in editor.options.veInlineClasses ){
      //  var title = editor.options.veInlineClasses[cls];
      //  if( cls==='link' ) {
      //    toolbar.push('link unlink');
      //  }
      //  else styles.push({title: title, inline:'span', classes:cls});
      //}
      //if (styles.length) {
      //  // スタイル設定あり
      //  toolbar.splice(0, 0, 'styleselect');
      //  toolbar.push('removeformat');
      //}

      //toolbar = toolbar.join(' | ');


      var toolbar = 'undo redo | link unlink | removeformat';
      var styles  = editor.options.veStyleFormats;
      if ( styles.length ){
          // toolbar = 'undo redo | styleselect link unlink | removeformat';
          toolbar = 'styleselect link unlink | removeformat';
      }

      console.log(toolbar);

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
              self.veInited = true;
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

      // 入力HTMLの取得
      var html = DOM(this.$refs.inputElement).html();
      if(html && (html.match(/^\s+$/) || html.match(/^\s*(<br\s*\/?>|<br\s+[^>]+\/?>)\s*$/)) ){ 
        // スペースのみか、改行単体のみの入力
        html = ''; // 入力なしとみなす
      }
      this.$emit('input', html);
    },

    // テキスト入力エリアフォーカスイベント
    onFocus: function(ev){
      var elm = DOM(this.$refs.inputElement);
      if( editor.options.placeholder && elm.text() === editor.options.placeholder ){
        // プレースホルダが入力されている場合はクリアする
        this.placeholded = false;
        elm.html('<br>');
      }
    },

    // テキスト入力エリアのフォーカス解除イベント
    onBlur: function(ev){
      this.onTextChange(ev);

      var elm = DOM(this.$refs.inputElement);
      if( editor.options.placeholder && !elm.text() ){
        // 入力がない場合はプレースホルダを入力する
        this.placeholded = true;
        setTimeout(function(){
          elm.html(editor.options.placeholder);
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


    var inputValue = elm.value();
    var inputName = elm.name();
    // _d(inputValue);
    // _d(inputName);

    if( typeof Vue === 'undefined' ){ // Vueライブラリのロードがされていない
      var scripts = [SCRIPT_DIR_PATH+'lib/vue/vue.min.js'];
      loadScript(scripts, 
        function(){ // Vueライブラリロード完了
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
        <div class="inline-editor">\
          <visualeditor v-bind:value="inputValue" v-on:input="onTextChange" />\
          <input v-bind:name="inputName" type="hidden" ref="sendData" />\
        </div>',
        data: function(){
          return {
            inputName: null,
            inputValue: null
          }
        },
        components: {
          visualeditor: VEComponent
        },
        created: function(){
          this.inputName = inputName;
          this.inputValue = inputValue;
        },
        mounted: function(){
          // フォーム送信イベントを設定
          var form = DOM(this.$el).closest('form');
          var self = this;
          if( form ){
            form.bind('submit', function(ev){
              var send = self.inputValue;
              DOM(self.$refs.sendData).value( send );
              // ev.preventDefault();
            });
          }

          if( editor.options.onLoad ){
            // 設定完了コールバックを呼ぶ
            editor.options.onLoad.call(editor);
          }
          
        },
        methods: {
          // VE上でのテキスト入力イベント
          onTextChange: function(ev){
            // 親のプロパティ更新
            // _d(ev);
            this.inputValue = ev;
          }
        }
      });

    }

  }

  // ユーティリティライブラリ ///////////////////////////////////////////////

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
  html = html.replace(/^<br\s*\/?>/i, ''); // 先頭のbr削除
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
  var ret = {};
  for( var k in hash ){
    ret[k] = hash[k];
  }
  return ret;
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

  // DOM操作ライブラリ /////////////////////////////////////////////////////
var DOM = function( selector ){
  if( selector instanceof DOM ) return selector;
  if( !(this instanceof DOM) ){
    return new DOM(selector);
  }
  if( DOM.isHTMLDocument(selector) || DOM.isHTMLElement(selector) ){
    this.root = selector;
  }else if( DOM.isHTML(selector) ){ // 引数がHTML
    var tmp_dom = document.createElement('div');
    tmp_dom.innerHTML = selector;
    this.root = tmp_dom.children[0];
  }else{
    this.root = document.querySelector(selector);
  }
  if( this.root )  this.tagName = this.root.tagName;
  else this.tagName = null;

  return this;
};

// classNameを持つか調べる
DOM.prototype.hasClass = function( className ){
  if( !this.root ) return false;
  return this.root.classList.contains( className );
};

// classNameを追加する
DOM.prototype.addClass = function( className ){
  if( !this.root ) return null;
  if( this.hasClass( className ) ) return this;
  this.root.classList.add( className );
  return this;
};

// classNameを削除する
DOM.prototype.removeClass = function( className ){
  if( !this.root ) return null;
  this.root.classList.remove( className );
  return this;
};

// HTMLを子ノードとして追加する
DOM.prototype.append = function( html ){
  if( !this.root ) return null;
  var dom;
  if( html instanceof DOM ) dom = html.HTMLElement();
  else dom = DOM.HTML2Element(html);

  this.root.appendChild( dom );
  return this;
};

// 自分自身をDOMから削除
DOM.prototype.remove = function(){
  if( !this.root ) return null;
  if( this.root ) this.root.parentNode.removeChild( this.root );
  return null;
};

// 含まれるHTMLを取得/設定する
DOM.prototype.html = function( html ){
  if( !this.root ) return null;
  if( typeof html==='undefined' ){ // getter
    return this.root.innerHTML;
  }else{ // setter
    this.root.innerHTML = html;
    return this;
  }
}

// 含まれるHTMLを取得/設定する
DOM.prototype.text = function( text ){
  if( !this.root ) return null;
  if( typeof text==='undefined' ){ // getter
    return this.root.textContent;
  }else{ // setter
    this.root.textContent = text;
    return this;
  }
}

// 値を取得
DOM.prototype.value = function( value ){
  if( !this.root ) return null;

  if( typeof value==='undefined' ){ // getter
    return this.root.value;
  }else{ // setter
    this.root.value = value;
    return this;
  }
}

// 名前を取得
DOM.prototype.name = function( value ){
  if( !this.root ) return null;

  if( typeof value==='undefined' ){ // getter
    return this.root.name;
  }
  return this;
}

// selectorに該当する最初のHTMLElementを取得する
DOM.prototype.findFirst = function( selector ){
  if( !this.root ) return null;
  var dom = this.root.querySelector( selector );
  if( dom ) return new DOM(dom);
  else return null;
};

// selectorに該当するすべてのHTMLElementを取得する
DOM.prototype.findAll = function( selector ){
  if( !this.root ) return null;
  var dom = this.root.querySelectorAll( selector );
  var ret = [];
  for( var i=0; i<dom.length; i++ ){
    var c = dom[i];
    ret.push(DOM(c));
  }
  return ret;
};

DOM.prototype.closest = function( selector ){
  if( !this.root ) return null;

  // 各ベンダー依存のプレフィックス考慮
  var matches_func;
  ['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn){
    if( typeof document.body[fn] === 'function' ){
      matches_func = fn;
      return true;
    }
    return false;
  });

  var base = this.root;
  while(1){
    var parent = base.parentNode;
    if( !parent || typeof parent[matches_func]==='undefined' ) break;

    if( parent[matches_func](selector) ){
      return DOM(parent);
    }
    base = parent;
  }
  return null;
};

// 属性値を取得/設定する
DOM.prototype.attr = function( attr, val ){
  if( !this.root ) return null;

  if( typeof val==='undefined' ){ // getter
    return this.root.getAttribute( attr );
  }else{ // setter
    this.root.setAttribute( attr, val );
    return this;
  }
};

// 子要素の取得
DOM.prototype.children = function(){
  if( !this.root ) return null;
  var children = this.root.children;

  var ret = [];
  for( var i=0; i<children.length; i++ ){
    var c = children[i];
    ret.push(DOM(c));
  }
  return ret;
};

// イベントをバインドする
DOM.prototype.bind = function( event, callback ){
  if( !this.root ) return null;
  this.root.addEventListener( event, callback );
  return this;
};
// イベントをアンバインドする
DOM.prototype.unbind = function( event, callback ){
  if( !this.root ) return null;
  this.root.removeEventListener( event, callback );
  return this;
};
DOM.prototype.trigger = function( event ){
  if( !this.root ) return null;
  if( document.createEvent ){
    // IE以外
    var evt = document.createEvent('HTMLEvents');
    evt.initEvent(event, true, true ); // event type, bubbling, cancelable
    return this.root.dispatchEvent(evt);
  }else{
    // IE
    var evt = document.createEventObject();
    return this.trigger.fireEvent(event, evt);
  }
  return this;
};

// HTMLElementを取得する
DOM.prototype.HTMLElement = function(){
  if( !this.root ) return null;
  return this.root;
};

// 要素の表示
DOM.prototype.show = function(){
  if( !this.root ) return null;
  this.root.style.display = 'block';
  return this;
};
// 要素の非表示
DOM.prototype.hide = function(){
  if( !this.root ) return null;
  this.root.style.display = 'none';
  return this;
};

// target_selectorが示す要素に、selectorが示す要素が含まれるか
DOM.exists = function( selector, target_selector ){
  if( typeof target_selector === 'undefined') target_selector = 'body';
  var dom = DOM.findFirst( selector, target_selector );
  return dom ? true : false;
};

// HTMLをHTMLElementに変換する
DOM.HTML2Element = function( html ){
  if( DOM.isHTMLElement(html) ) return html;
  var wrap = document.createElement('div');
  wrap.innerHTML = html;
  return wrap.children[0];
};

// 引数がHTMLElementかどうか
DOM.isHTMLElement = function( val ){
  return val instanceof HTMLElement;
};

// 引数がHTMLDocumentかどうか
DOM.isHTMLDocument = function( val ){
  if( typeof HTMLDocument !== 'undefined' ) return val instanceof HTMLDocument;
  if( typeof Document !== 'undefined' ) return val instanceof Document; // IE10用
}

// 引数がHTMLElementかどうか
DOM.isHTML = function( val ){
  return val.match(/<.+?>/);
}

  return function(selector, options){
    if( !SUPPORTED ){
      alert('[InlineEditor] お使いのブラウザはサポートされていません');
      return null;
    };

    // selectorにマッチした要素を全て取得し、
    // AttachUploadを適用する
    this.instances = [];
    var elms = DOM('body').findAll(selector, options);
    var len = elms.length;
    for(var i=0; i<len; i++){
      var editor = new InlineEditor(elms[i].root, options);
      this.instances.push( editor );
    }
  };

})();

