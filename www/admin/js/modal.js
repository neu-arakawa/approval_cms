function ModalCtl(options){
    var defaults = {
        'base_url': '/'
    };
    options = $.extend(defaults, options);

    var self = this;
    var $html = $('html');

    // data-modal-search-urlが設定されているボタンのクリックイベント
    $('button[data-modal-search-url]').bind('click',function(){
        if(ModalCtl.busy) return false; // 既にオーバーレイが表示中なら終了
        ModalCtl.busy = true;
        var $modal_btn = $(this);
        fixBG(true);    // 背景固定

        var url = $(this).attr('data-modal-search-url');
        var $overlay = $('<div class="overlay"></div>');
        $overlay
            .bind('click',function(){ // オーバーレイのクリックイベント
                // オーバーレイを非表示、削除する
                $overlay.remove();
                ModalCtl.busy = false;
                // 背景固定を解除
                fixBG(false);
                return false;
            });

        var $loading = $('<div class="loading"><img src="'+ options['base_url'] +'/admin/img/loading.gif" /></div>');
        var $close = $('<button class="btn-close btn btn-xs btn-secondary"><i class="fas fa-times"></i></button>');
        $close.bind('click', function(){ // オーバーレイの閉じるボタンクリックイベント
            $overlay.trigger('click');
        });

        $frame_wrap = $('<div class="frame-wrap"><iframe scrolling="auto"></iframe></div>');
        $frame_wrap
            .append($loading)
            .append($close)
            .find('iframe')
            .attr('src', url)
            .bind('load',function(){
                $loading.fadeOut(100);
            });

        $overlay.append($frame_wrap);
        $('body').prepend($overlay);

        // 値の選択イベント
        window.onModalSelect = function(val){
            var $parent = $modal_btn.closest('td, li');
            $parent.find();

            var $label_target = $parent.find('[data-modal-label-target]');
            var $id_target = $parent.find('[data-modal-id-target]');
            $id_target.text(val.id).val(val.id);
            $label_target.text(val.label).val(val.label);

            initButtons();

            $overlay.trigger('click'); // オーバーレイ非表示
            window.onModalSelect = null;
        }

        return false;
    });

    // クリアボタンクリックイベント
    $('button[data-modal-action="clear"]').bind('click',function(){
        var $parent = $(this).closest('td, li');
        var $label_target = $parent.find('[data-modal-label-target]');
        var $id_target = $parent.find('[data-modal-id-target]');

        $label_target.text('').val('');
        $id_target.text('').val('');

        initButtons();

        return false;
    });

    // 背景のスクロール固定
    function fixBG(fix){
        if(fix){
            $html.css('overflow', 'hidden');
        }else{
            $html.removeAttr('style');
        }
    }

    var $buttons = $('button[data-modal-search-url]'); // モーダルと関連づけられているボタン要素

    // ボタン初期化処理
    function initButtons(){
        $('[data-modal-id-target]').each(function(){
            var $parent = $(this).closest('td, li');
            var $label_target = $parent.find('[data-modal-label-target]');
            var $id_target = $parent.find('[data-modal-id-target]');
            var $label_input_target = $parent.find('input[data-modal-label-target]');
            var $del_button = $parent.find('[data-modal-action=clear]');

            var label_val = $label_input_target.val();
            if( $id_target.val() ){
                // ボタンに紐付く選択値が設定されている
                $del_button.show(); // 削除ボタンを表示
                $label_target.text(label_val).val(label_val);
            }else{
                $del_button.hide(); // 削除ボタンを非表示
                $label_target.text('').val('');
            }
        });
    }
    initButtons();
}
ModalCtl.busy = false;

function ModalSelect(){
    var $buttons = $('button[data-modal-select]');
    $buttons.bind('click', function(){
        var id = $(this).attr('data-modal-select-id');
        var label = $(this).attr('data-modal-select-label');

        if(window.parent.onModalSelect){ // 呼び出し元の関数呼び出し
            window.parent.onModalSelect({id:id,label:label});
        }
    });

    $buttons.closest('tr')
        .css({cursor: 'pointer'})
        .bind('mouseover', function(){ $(this).addClass('active') })
        .bind('mouseout', function(){ $(this).removeClass('active') })
        .bind('click', function(){
            $(this).find('button[data-modal-select]').trigger('click');
        });
}

