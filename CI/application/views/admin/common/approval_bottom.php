<?php $settings = [];?>

<?php if( !empty($current_data) && in_array( $current_data['status'], [ APPROVAL_STATUS_PUBLISHED, APPROVAL_STATUS_PRIVATE ] ) ){ //通常 ?>

    <div class="edit-box publish_block">
        <h3>公開設定</h3>
        <div>
            <?php if (!$confirm_flag): // 入力画面?>
            <div class="inline publish_flag">
                <?php echo admin_form_radio_raw('status', [ APPROVAL_STATUS_PUBLISHED=>'公開', APPROVAL_STATUS_PRIVATE=>'公開取下げ'], 1, ['empty'=>false]);?>
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
    <?php 
        $settings['reject']   = false;
        $settings['approved'] = false;
        $settings['pending']  = false;
    ?>

<?php }else if( !empty($current_data) && $current_data['status'] == APPROVAL_STATUS_PENDING ){ ?>

        <?php if( $this->my_session->admin->flg_approval ){ ?>
	        <div class="edit-box">
	            <h3>承認結果通知メール</h3>
	            <div class="row">
	                <div class="col-lg-6">
	                    <?php echo admin_form_textarea_raw('note', null, ['label'=>'メッセージ', 'placeholder'=>'承認結果メールに添えるメッセージがあれば、ご記入ください（任意）'], 'col-lg-12');?>
	                </div>
	                <div class="col-lg-6">
	        			<?php if( !empty($current_data['notes']) ) { ?>
	                    <div style="overflow-y:scroll;max-height:250px" class='bg-dark text-white p-3'>
	                        <?php foreach($current_data['notes'] as $note){?>
	                        <div><small><?php echo date('Y/m/d H:i', strtotime($note['created'])) ?>: <?php echo h($note['author']) ?></small><div class='mb-3 pl-2'><?php echo nl2br(h($note['note'])) ?> </div></div>
	                        <?php } ?>
	                    </div>
	                    <?php } ?>
	                </div>
	            </div>
	        </div>
            
            <?php 
                $settings['draft'] = false;
                $settings['direct'] = false;
                $settings['pending_midflow'] = '途中保存する';
            ?>
        <?php }else { ?>
	        <div class="edit-box" id="wrap_pending_cancel">
	            <h3>承認依頼を取り下げ</h3>
	            <div class="text-center">
                  <p>承認依頼中の記事を取り下げることができます。</p>
	              <button type="button" name="btn_pending_cancel" class="btn btn-primary" data-confirm="<h5>承認依頼を取り下げますか</h5><small class='red'>※ 承認依頼を取り下げると、再度承認依頼ができます</small>">
	              承認依頼を取り下げる
	              </button>
	            </div>
	        </div>
            <div class="form-overlay"></div>
            <style>
            .row.edit-buttons{display:none}
            .form-wrapper {
              position: relative;
              display: inline-block; /* 必要に応じて調整 */
            }
            
            .form-overlay {
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background-color: rgba(255, 255, 255, 0.5); /* ← 透明度調整可能 */
              z-index: 10;
            }
            #wrap_pending_cancel {
              position: absolute;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%);
              z-index: 20;
              background-color: rgba(255, 255, 255, 0.95); /* 半透明白背景など */
              padding: 12px 20px;
              border: 1px solid #ccc;
              border-radius: 6px;
              text-align: center;
              box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            </style>
        <?php } ?>

<?php }else { ?>

    <?php if( $this->my_session->admin->flg_approval ){ ?>
        <?php 
            $settings['direct']   = false;
            $settings['reject']   = false;
            $settings['approved'] = '承認を無視して公開する';
            $settings['pending']  = false;
        ?>

    <?php }else{ ?>
	    <div class="edit-box">
	        <h3>承認依頼通知メール</h3>
	        <div class="row">
	            <div class="col-lg-6">
	            <?php echo admin_form_textarea_raw('note', null, ['label'=>'メッセージ', 'placeholder'=>'承認依頼メールに添えるメッセージがあれば、ご記入ください（任意）'], 'col-lg-12');?>
	            </div>
	            <div class="col-lg-6">
	    			<?php if( !empty($current_data['notes']) ) { ?>
	                <div style="overflow-y:scroll;max-height:250px" class='bg-dark text-white p-3'>
	                    <?php foreach($current_data['notes'] as $note){?>
	                    <div><small><?php echo date('Y/m/d H:i', strtotime($note['created'])) ?>: <?php echo h($note['author']) ?></small><div class='mb-3 pl-2'><?php echo nl2br(h($note['note'])) ?> </div></div>
	                    <?php } ?>
	                </div>
	                <?php } ?>
	            </div>
	        </div>
	    </div>
        <?php 
            $settings['direct']   = false;
            $settings['reject']   = false;
            $settings['approved'] = false;
            $settings['pending']  = '承認依頼として送信する';
        ?>
    <?php } ?>

<?php } ?>

<?php if (!$confirm_flag): // 入力画面?>
<script src="/admin/js/bootbox.min.js"></script>
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
    var $flg_radio = $('input[type="radio"]').filter('[name="status"]');
    var $publish_term_block = $('.publish_term');
    var $term_radio = $('input[type="radio"]').filter('[name="publish_term"]');

    var $draft_btn = $('button[name="btn_draft"]');
    var $confirm_btn = $('button[name="btn_confirm"]');

    var $start_date = $('input[type="text"]').filter('[name="start_date"]');
    var $end_date = $('input[type="text"]').filter('[name="end_date"]');
    var $term_elm = $('.term_elm');


    if( $flg_radio.length ){
        //. 公開/公開取下げラジオボタン変更
        $flg_radio.bind('change', function(){
            _refresh_publish();
        });
    }

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
        if (flg=='<?= APPROVAL_STATUS_PUBLISHED ?>') {
            $publish_term_block.show();
            $draft_btn.hide();
            $confirm_btn.show();
            // $('button[name="btn_direct"]').show();
        } else {
            $publish_term_block.hide();
            $draft_btn.hide();
            $confirm_btn.hide();
            // $('button[name="btn_direct"]').hide();
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
    if( $flg_radio.length ) _refresh_publish();

    $('[name=btn_pending],[name=btn_pending_cancel],[name=btn_reject],[name=btn_approved]').click(function(){
        let self = this;
        bootbox.confirm($(this).data('confirm'), function(result){ 
            if(result){
                $(self).attr('type','submit');
                $(self).off("click");
                $(self).click();
            }
        });
    });
});

</script>
<?php endif?>

<?php 
if (empty($confirm_flag)) echo admin_input_buttons($settings);
else echo admin_confirm_buttons();
