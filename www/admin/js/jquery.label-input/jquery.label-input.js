(function($){
    $.fn.labelInput = function (options) {
        var def_options = {
            valueSeparator: '	',      // inputフィールド値の区切り文字
            minLabelLength: 2,          // 最小文字数
            maxLabelLength: 50,         // 最大文字数
            allowSameLabel: false,      // 同一ラベルを許可するか
            maxLabelNum: 50,            // 最大ラベル数
            sideMargin: 8,              // ラベルの横マージン(px)
            upDownMargin: 5,            // ラベルの縦マージン(px)
            minInputWidth: 150,         // 入力エリアの最低幅(px)
            closeSelector: '.close',    // 閉じるボタンのセレクタ
            labelClass: 'label',        // ラベルのクラス
            readOnly: 0                 // 読み込み専用（確認画面用）
        };
        var options = $.extend(def_options, options);

        this.each(function () {
            new LabelInput($(this), options);
        });
    };

    function LabelInput ($self, options) {
        this.options = options;
        this.$self = $self;
        this.$root = null;
        this.$inputWrap = null;
        this.$input = null;
        this.rootPaddingLeft = 0;
        this.rootPaddingRight = 0;

        this.init();
    }

    // 初期化処理
    LabelInput.prototype.init = function () {
        
        var input_value = this.$self.val();

        var labels = [];
        if (input_value) {
            labels = input_value.split(this.options.valueSeparator);
        }
        labels = $.grep(labels, function(e){return e!== ""});
        
        // 要素の前に、入力用のフィールドを設置する
        var tag = '';
        if (this.options.readOnly) { // 読み込み専用
            tag = '<ul class="label-input readonly"></ul>';
        } else {
            tag = '<ul class="label-input"><li class="input"><input type="text"></li></ul>';
        }
        
        this.$root = $(tag);
        this.$inputWrap = this.$root.find('.input');
        this.$input = this.$inputWrap.find('input');

        this.$self.before(this.$root);

        this.rootPaddingLeft = parseInt(window.getComputedStyle(this.$root[0]).paddingLeft);
        this.rootPaddingRight = parseInt(window.getComputedStyle(this.$root[0]).paddingRight);

        // 初期ラベルの追加処理
        for(var i=0; i<labels.length; i++) {
            this.addLabel(labels[i], false);
        }

        // 閉じるボタンクリック
        var self = this;
        this.$root.on('click', this.options.closeSelector, function () {
            self.onCloseClick.call(self, $(this));
        });
        // キー入力
        this.$input.bind('keypress', function(ev){
            return self.onFixKeyPress.call(self, ev);
        });
        // エリア全体クリック
        this.$root.bind('click', function () {
            self.$input.focus();
            return false;
        });
        this.$input.bind('focus', function(){
            self.$root.addClass('focus');
        });
        this.$input.bind('blur', function(){
            self.$root.removeClass('focus');
        });
        this.$root.on('dblclick', '.'+this.options.labelClass, function () {
            self.editLabel.call(self,$(this));
        });

        // 画面リサイズ
        $(window).bind('resize', function () {
            self.adjustInput.call(self);
        });
        this.adjustInput();
    };

    // 閉じるボタンクリック時
    LabelInput.prototype.onCloseClick = function ($elm) {
        this.removeLabel($elm.closest('.'+this.options.labelClass));
        this.adjustInput();
    };

    // 入力エンター
    LabelInput.prototype.onFixKeyPress = function (e) {
        if (e.keyCode === 13) {
            // Enter

            var val = this.$input.val();
            if (!val) return false;

            if (val.length < this.options.minLabelLength) {
                alert(this.options.minLabelLength+'文字以上で入力してください');
                return false;
            }
            if (val.length > this.options.maxLabelLength) {
                alert(this.options.maxLabelLength + '文字以下で入力してください');
                return false;
            }
            var labels = this.getAllLabels();
            if (!this.options.allowSameLabel && labels.indexOf(val)!==-1) { // 同一ラベル有り
                alert('同一ラベルが入力されています');
                return false;
            }
            if (labels.length >= this.options.maxLabelNum) {
                alert('ラベルの入力数が最大数に達しています');
                return false;
            }

            // ラベルを追加
            this.addLabel(val);
            this.adjustInput();

            // 入力をクリア
            this.$input.val(''); 

            return false;
        }
        return true;
    }

    LabelInput.prototype.getAllLabels = function () {
        var $labels = this.$root.find('.' + this.options.labelClass);

        var arr = [];
        $labels.each(function () {
            arr.push($(this).text());
        });
        return arr;
    },

    // ラベル追加処理
    LabelInput.prototype.addLabel = function (text, reflect) {
        if (typeof reflect === 'undefined') reflect = true;
        text = text.trim();
        var $prev = this.$inputWrap.prev();
        var tag = '';
        if (this.options.readOnly) { // リードオンリー
            tag = '<li class="' + this.options.labelClass + '" style="margin-right:' + this.options.sideMargin + 'px">' + text + '</li>';
        } else {
            tag = '<li class="' + this.options.labelClass + '" style="margin-right:' + this.options.sideMargin + 'px">' + text + '<span class="close"></span></li>';
        }
        
        if (this.options.readOnly) {
            this.$root.append(tag);
        } else if ($prev.length) { // 入力の前の要素あり
            $prev.after(tag);
        } else { // 入力の前の要素なし
            this.$root.prepend(tag);
        }

        if (reflect) this.reflect(); // ラベルを入力に反映
    };

    // ラベルの再編集
    LabelInput.prototype.editLabel = function ($label) {
        var val = $label.text();
        this.removeLabel($label, false);
        this.$input.val(val);
    };

    // ラベル削除処理
    LabelInput.prototype.removeLabel = function ($label, reflect) {
        if (typeof reflect === 'undefined') reflect = true;

        $label.remove();
        if (reflect) this.reflect(); // ラベルを入力に反映
    };

    // ラベルを元の入力エリアに反映する
    LabelInput.prototype.reflect = function () {
        var arr = this.getAllLabels();
        if (arr && arr.length) {
            this.$self.val(arr.join(this.options.valueSeparator));
        } else {
            this.$self.val('')
        }
    };

    // 入力エリア調整
    LabelInput.prototype.adjustInput = function () {
        
        var rootWidth = this.$root.outerWidth();
        var innerWidth = rootWidth - this.rootPaddingLeft - this.rootPaddingRight;

        // 入力要素の直前のラベル要素を取得
        var $prev = this.$inputWrap.prev();
        var rest_width = 0;
        if ($prev.length) {
            var prev_right = $prev.position().left - this.rootPaddingLeft + $prev.outerWidth();
            rest_width = innerWidth - prev_right - this.options.sideMargin - 10;
        } else {
            rest_width = innerWidth;
        }

        // console.log({
        //     rootWidth: rootWidth,
        //     rootPaddingLeft: this.rootPaddingLeft,
        //     rootPaddingRight: this.rootPaddingLeft,
        //     innerWidth: innerWidth,
        //     restWidth: rest_width,
        //     prevWidth: $prev.outerWidth(),
        //     prevRight: $prev.position().left - this.rootPaddingLeft + $prev.outerWidth()
        // });
        if (rest_width < this.options.minInputWidth) {
            this.$inputWrap.width(innerWidth);
        } else {
            this.$inputWrap.width(rest_width);
        }

    };

})(jQuery);
