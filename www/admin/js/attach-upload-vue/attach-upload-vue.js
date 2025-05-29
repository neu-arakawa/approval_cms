/*
 * AttachUpload
 * ver. 1.02
 * 
 * require IE >= 10
 *
 */
var AttachUpload = (function(){
  
  var DEBUG = true;
  var SCRIPT_DIR_PATH = getScriptPath();

  var FILE_API_ENABLED = window.File && window.FileReader && window.FileList && window.Blob;
  var BROWSER = getBrowser();
  var SUPPORTED = ( BROWSER=='edge' || BROWSER=='chrome' || BROWSER=='safari' || BROWSER=='firefox' ||
          (BROWSER=='ie' && getIEVersion()>=10 ) ) && FILE_API_ENABLED;

  // CSSを動的にロード
  loadCSS(SCRIPT_DIR_PATH + 'css/style.css');
  loadCSS(SCRIPT_DIR_PATH + 'lib/font-awesome/font-awesome.min.css');

  var AttachUpload = function(element, options){
    var attach = this;

    var elm = DOM( element );

    // デフォルトのオプション設定　//////////////////////////////////////////
    var defaultOptions = {
      'maxFileSize':'2MB',              // アップロード可能な最大ファイルサイズ（サーバサイドの設定値よりも小さい値を入れること）
      'onlyImage': false,               // 画像のみ許可（trueの場合、設定されたallowTypesは無視されます）
      'allowTypes': ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','mp4','txt','jpg','jpeg','jpe','gif','png'],
      // 許可するファイルの種類（指定しない場合はnull）
      'uploaderDomain': null,           // Uploaderプラグインのドメイン（このプラグインと別ドメインで稼働している場合）
      'uploaderPath': '/uploader',      // Uploaderプラグインのパス(Web)
      'uploaderDir': '/',               // Uploaderプラグインのアップロードディレクトリ名(/始まり)
      'onLoad': function(){}            // 要素のセットアップが完了して使用可能になったときのコールバック
    };

    var imageTypes = ['jpg','jpeg','jpe','gif','png'];

    // 設定値のセットアップ ////////////////////////////////////////////////
    this.options = cloneHash( defaultOptions );

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

    if( this.options.onlyImage ){
      this.options.allowTypes = imageTypes;
    }

    // アップローダのパスの末尾のスラッシュを補う
    if( !this.options.uploaderPath.match(/\/$/) ) this.options.uploaderPath = this.options.uploaderPath+'/';
    // アップロードディレクトリ名の先頭と末尾のスラッシュを除去する
    if( this.options.uploaderDir.match(/^(\/*)(.*?)(\/*)$/) ) this.options.uploaderDir = RegExp.$2;
    // アップロードドメインの末尾のスラッシュを除去する
    if( this.options.uploaderDomain && this.options.uploaderDomain.match(/^(.*?)(\/*)$/) ) this.options.uploaderDomain = RegExp.$1;

    // console.log(this.options);

    // コンポーネントの定義 ////////////////////////////////////////////////
    // 画像コンポーネント ///////////////////////////////////////////////////
var imageComponent = {
  template: '\
  <div class="img" v-on:dblclick="onFileSelect">\
    <img v-bind:src="file" draggable="false" />\
  </div>',
  props: ['file'],
  methods: {
    onFileSelect: function(){
      this.$emit('file-select');
    }
  }
};
    // ファイルコンポーネント ///////////////////////////////////////////////////
var fileComponent = {
  template: '\
  <div class="file" v-on:dblclick="onFileSelect">\
    <a v-bind:href="file" target="_blank" draggable="false">{{fileName}}</a>\
  </div>',
  props: ['file'],
  computed: {
    fileName: function(){
      if( this.file ){
        if( this.file.match(/([^\/]+?)$/) ) return RegExp.$1;
      }
      return null;
    }
  },
  methods: {
    onFileSelect: function(){
      this.$emit('file-select');
    }
  }
};

    // POST値を解析して要素を配置する処理 ////////////////////////////////////
    var inputValue = elm.value();
    var inputName = elm.name();
    // _d(inputValue);
    // _d(inputName);

    if( typeof Vue === 'undefined' ){ // Vueライブラリのロードがされていない
      var scripts = [SCRIPT_DIR_PATH+'lib/vue/vue.min.js'];
      loadScript(scripts, 
        function(){ // Vueライブラリロード完了
          init.call(attach); // 初期化処理実行
        },
        function(){ // エラー
          alert('[AttachUpload] ライブラリの読み込みに失敗しました');
        });
    }else{
      init.call(attach);
    }
    
    // 初期化処理 /////////////////////////////////////////////////////////////
    function init(){
      if( this.vue ) return; // すでに初期化済みなら終了
      this.vue = new Vue({
        el: element,
        template: '\
        <div class="attach-upload" v-on:dragover.prevent.stop="onDragover" v-on:dragleave="onDragleave" v-on:drop.prevent.stop="onDrop" v-bind:class="{dragover:dragover}" >\
          <div class="loading" v-show="isLoadingFile"><div class="progress-wrap"><div class="progress" v-bind:style="{width: uploadProgress + \'%\'}"></div></div></div>\
          <div class="dnd" v-show="isDndSupported && !filePath" v-on:dblclick="selectFile">\
            <div class="msg">{{attachLabel}}をドラッグ＆ドロップ</div>\
          </div>\
          <div class="content" v-show="filePath">\
            <transition name="attach" mode="out-in">\
            <component v-bind:is="attachComponent" v-bind:file="filePath" v-on:file-select="selectFile" />\
            </transition>\
          </div>\
          <button v-show="filePath" class="remove" tabindex="-1" v-on:click.prevent="removeFile">{{attachLabel}}をクリア</button>\
          <button class="uploader" tabindex="-1" v-on:click.prevent="selectFile">アップロード済みの{{attachLabel}}から選択</button>\
          <input v-bind:name="inputName" type="hidden" v-bind:value="filePath" />\
        </div>',
        data: function(){
          return {
            inputName: inputName,
            filePath: null,
            isDndSupported: attach.options.uploaderDomain ? false : true,
            dragover: false,
            isLoadingFile: false,
            uploadProgress: 0,
            attachComponent: null,
            attachLabel: attach.options.onlyImage ? '画像' : 'ファイル'
          }
        },
        components: {
          fileCmp: fileComponent,
          imageCmp: imageComponent
        },
        created: function(){
          this.setFile(inputValue);
        },
        mounted: function(){
          // 設定完了コールバックを呼ぶ
          this.$nextTick(function(){
            attach.options.onLoad.call(attach);
          });
        },
        methods: {

          // ファイルのドラッグオーバー
          onDragover: function(ev){
            if( !this.isDndSupported ) return false;

            ev.dataTransfer.dropEffect = 'copy';
            this.dragover = true;
          },

          // ファイルのドラッグリーブ
          onDragleave: function(ev){
            if( !this.isDndSupported ) return false;

            this.dragover = false;
          },

          // ファイルのドロップ
          onDrop: function(ev){
            if( !this.isDndSupported ) return false;

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
            if( !error && file.size > sizeToByte(attach.options.maxFileSize) ) error = 'ファイルのサイズが上限('+attach.options.maxFileSize+')を超過しています';

            if( !error && !this._isAcceptable(file.name) ){
              error = 'ファイルの種類は ' + attach.options.allowTypes.join('・') + ' のいずれかである必要があります';
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
            AttachUpload.upload.call(
              this,
              file, 
              attach.options.uploaderPath,
              attach.options.uploaderDir,
              onSuccess,
              onError,
              progress
            );

          },

          // ファイルの設定
          setFile: function(file){
            if( file && !this._isAcceptable(file) ){
              // 許可しないファイル
              alert('ファイルの種類は ' + attach.options.allowTypes.join('・') + ' のいずれかである必要があります');
              return false;
            }

            if( this._isImage(file) ){// 画像
              this.attachComponent = 'imageCmp';
            }else{
              this.attachComponent = 'fileCmp';
            }
            this.filePath = file;
            this.dragover = false;
            this.isLoadingFile = false;
          },

          // ファイルのクリア
          removeFile: function(){
            this.filePath = null;
          },

          // アップローダボタンクリック 
          selectFile: function(ev){
            // アップローダの表示
            AttachUpload.showUploader(
              attach.options.uploaderDomain, 
              attach.options.uploaderPath, 
              attach.options.uploaderDir, 
              this.setFile 
            );
            return false;
          },

          // 画像かどうかの判定
          _isImage: function(file){
            var reg = new RegExp('\.('+ imageTypes.join('|') +')$');
            if( file.match( reg ) ){
              return true;
            }
            return false;
          },

          _isAcceptable: function(file){
            var acceptable = attach.options.allowTypes;
            if( acceptable ){
              var reg = new RegExp('\.('+ acceptable.join('|') +')$');
              if( !file.match( reg ) ) return false;
            }
            return true;
          }

        }
      });

    }

  }

  // アップローダモーダルの表示 /////////////////////////////////////////////////////
  var currOverlay = null;
  AttachUpload.showUploader = function( domain, path, dir, callback ){
    // console.log('showUploader');
    if( currOverlay ) return false; // アップローダが既に表示されている

    // デフォルトで表示するディレクトリを指定
    domain = domain ? domain : '';
    var uploader_path = path;
    if( domain ) uploader_path = domain + uploader_path;
    if( dir ) uploader_path += '#' + dir;

    currOverlay = DOM('<div class="attach-upload-overlay"><div class="uploader"><button class="close"><i class="fa fa-times" aria-hidden="true"></i></button><iframe src="'+uploader_path+'"></iframe></div></div>');
  
    // アップローダーのポップアップウィンドウを作成・表示
    currOverlay
      .addClass('active')
      .bind('click', AttachUpload.hideUploader);

    DOM('body').append(currOverlay);

    // Uploaderからのデータ受け取り(通常時)
    if( !window.selectUploadFile ){
      window.selectUploadFile = function( file_path ){ // ファイルが選択された
        if( callback ) callback(domain+file_path);
        AttachUpload.hideUploader(); // アップローダを非表示に
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
        AttachUpload.hideUploader();
      }
    }
  
  }

  // アップローダモーダル非表示 ////////////////////////////////////////////////////
  AttachUpload.hideUploader = function(){
    if( !currOverlay ) return false; // アップローダが表示されていない
    currOverlay.removeClass('active');

    window.selectUploadFile = null;
    window.onmessage = null;

    // アニメーション速度を考慮し、一定時間後に要素を削除
    setTimeout(function(){
      currOverlay.remove();
      currOverlay = null;
    }, 500);
  };

  // アップロード処理 ///////////////////////////////////////////////////////////
  AttachUpload.upload = function( file, path, dir, callback, err_callback, prg_callback ){

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
    if( prg_callback ){
      xhr.upload.onprogress = function(ev){
         prg_callback(ev.loaded/ev.total);
      }
    }
    
    xhr.send( fd );

  };  

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

// キャメルケース(testProperty)からケバブケース(test-property)への変換
function kebabCase(camel) {
  return camel.replace(/[A-Z]/g, function(s){
    return '-' + s.charAt(0).toLowerCase();
  });
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
      alert('[AttachUpload] お使いのブラウザはサポートされていません');
      return null;
    };

    // selectorにマッチした要素を全て取得し、
    // AttachUploadを適用する
    this.instances = [];
    console.log(selector);
   
    if (selector instanceof HTMLElement) {
      var editor = new AttachUpload(selector, options);
      this.instances.push( editor );
    } else {
      var elms = DOM('body').findAll(selector, options);
      var len = elms.length;
      for(var i=0; i<len; i++){
        var editor = new AttachUpload(elms[i].root, options);
        this.instances.push( editor );
      }
    }
    
  };

})();

