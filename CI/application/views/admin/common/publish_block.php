<div class="edit-box publish_block">
    <h3>公開設定</h3>
    <div>
        <?php if (!$confirm_flag): // 入力画面?>
        <div class="inline publish_flag">
            <?php echo admin_form_radio_raw('flg_publish', [1=>'公開', 0=>'公開取下げ'], 1, ['empty'=>false]);?>
        </div>
        <div class="publish_term">
            <div class="form-row align-items-center align-top">
                <div class="col-auto align-top">
                    <label class="label">掲載期間設定</label>
                </div>
                <div class="col-md-10">
                    <div class="col-md-12">
                        <label class="">
                            <input name="publish_term" type="radio" value="0"> 指定しない
                        </label>
                    </div>
                    <div class="col-md-12">
                        <div class="form-row align-items-center">
                            <label class="radio col-auto">
                                <input name="publish_term" type="radio" value="1"> 指定する
                            </label>
                            <?php
                            if ($confirm_flag) $col_cls = 'col-auto';
                            else $col_cls = 'col-md-4';
                            ?>
                            <div class="term_elm <?php echo $col_cls?>"><?php echo admin_form_input_raw('start_date', null, ['class'=>'datetimepicker'])?></div>
                            <div class="term_elm col-auto"> 〜 </div>
                            <div class="term_elm <?php echo $col_cls?>"><?php echo admin_form_input_raw('end_date', null, ['class'=>'datetimepicker'])?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: // 確認画面?>
            <?php
            if (!empty($_POST['flg_publish'])) echo '記事を公開する';
            else echo '記事を公開取下げ保存する';
            ?>
            <br>
            <?php if (!empty($_POST['flg_publish'])):?>
                <label>掲載期間：</label>
                <?php
                $d = [];
                if (!empty($_POST['start_date'])) $d[] = h($_POST['start_date']);
                else $d[] = '';

                if (!empty($_POST['end_date'])) $d[] = h($_POST['end_date']);
                else $d[] = '';

                if (!empty(array_filter($d))) echo implode(' 〜 ', $d);
                else echo '指定しない';
                ?>
            <?php endif?>
        <?php endif?>
    </div>
</div>
<script>
$(document).ready(function(){

    $('#preview').click(function(e){
      let f = $(this).closest('form');
      f.attr('target', 'preview');
      let ori = f.attr('action');
      f.attr('action', '<?php echo !empty($preview_url) ? $preview_url : "/$controller/preview_changes" ?>');
      f.submit();
      f.attr('action', ori);
      f.attr('target', '_self');
    });

    var term_is_set = <?php echo !empty($_POST['start_date']) || !empty($_POST['end_date']) ? '1' : '0' ?>;
    var $flg_radio = $('input[type="radio"]').filter('[name="flg_publish"]');
    var $publish_term_block = $('.publish_term');
    var $term_radio = $('input[type="radio"]').filter('[name="publish_term"]');

    var $draft_btn = $('button[name="btn_draft"]');
    var $confirm_btn = $('button[name="btn_confirm"]');

    var $start_date = $('input[type="text"]').filter('[name="start_date"]');
    var $end_date = $('input[type="text"]').filter('[name="end_date"]');
    var $term_elm = $('.term_elm');

    //. 公開/公開取下げラジオボタン変更
    $flg_radio.bind('change', function(){
        _refresh_publish();
    });

    // 公開期間設定ラジオボタン変更
    $term_radio.bind('change', function(){
        _refresh_publish();
    });

    $start_date.bind('blur', function(){
        if ($start_date.val() || $end_date.val()) {
            $term_radio.filter('[value="1"]').prop('checked',true);
        }
    });
    $end_date.bind('blur', function(){
        if ($start_date.val() || $end_date.val()) {
            $term_radio.filter('[value="1"]').prop('checked',true);
        }
    });

    function _refresh_publish(){
        var flg = $flg_radio.filter(':checked').val();
        if (flg=='1') {
            $publish_term_block.show();
            $draft_btn.hide();
            $confirm_btn.show();
            $('button[name="btn_direct"]').show();
            $('button[name="btn_temporary"]').show();
        } else {
            $publish_term_block.hide();
            $draft_btn.show();
            $confirm_btn.hide();
            $('button[name="btn_direct"]').hide();
            $('button[name="btn_temporary"]').hide();
        }

        var term = $term_radio.filter(':checked').val();
        if (term=='1') { // 期間設定指定あり
            // $term_elm.show();
        } else if (term=='0'){ // 期間設定指定なし
            $start_date.val('');
            $end_date.val('');
            // $term_elm.hide();
        }
    }
    if (term_is_set) { // 期間設定あり
        $term_radio.filter('[value="1"]').prop('checked',true);
    } else { // 期間設定なし
        $term_radio.filter('[value="0"]').prop('checked',true);
    }
    _refresh_publish();

});


</script>
