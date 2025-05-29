var layeredSelectCtl = function(data, $selects){
    var self = this;
    self.data = data;
    self.$selects = $selects;

    // 項目の選択イベント
    self.$selects.bind('change', function(){
        var idx = self.$selects.index($(this));
        var data = self.data;
        for(var i=0; i<idx; i++){
            var $select = self.$selects.eq(i);
            var id = $select.val();
            if(id && data[id] && data[id]['sub']){
                data = data[id]['sub'];
            }
        }
        self.$selects.filter(':gt('+idx+')')
            .val('')   // 選択されたセレクト以下の階層の選択を解除
            .hide()
            .closest('.select').prev('.arrow').hide();
        self.selectProc(data, $(this));
    });

    // セレクト選択処理
    self.selectProc = function(data, $select){
        var idx = self.$selects.index($select);
        if(!idx){
            // 一番上の階層のセレクトの処理時
            // いったん全てのセレクト、矢印を非表示にする
            self.$selects
                .hide()
                .closest('.select').next('.arrow').hide();
        }

        var val = $select.val();                    // 現在の選択値取得
        if(!val) val = $select.attr('data-value');  // 選択済みでなければ、記録しておいた値を取得
        $select.removeAttr('data-value');

        $select
            .show()         // 表示
            .find('option:gt(0)').remove();   // optionを一旦クリア

        var tag = '';
        for(var id in data){
            var name = data[id]['name'];
            tag += '<option value="'+id+'">'+name+'</option>';
        }
        $select
            .append(tag)
            .val(val);

        var $next_select = self.$selects.eq(idx+1);
        if(data[val] && data[val]['sub'] && $next_select.length){
            $next_select.closest('.select').prev('.arrow').show();
            self.selectProc(data[val]['sub'], $next_select);
        }
    }

    self.selectProc(self.data, self.$selects.eq(0));
}