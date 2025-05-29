<?php /* 編集画面と確認画面共通のテンプレート */ ?>

<?php include_once VIEWPATH.'admin/common/info_block.php'; ?>

<div class="edit-box">
    <h3>基本設定</h3>
    <table class="table table-bordered dataTable">
        <tbody>
            <?php echo admin_form_row(!empty($model_id)?$model_id:'(自動で採番されます)', 'ID', null, 'col-md-8');?>
            <?php echo admin_form_input('disp_date', date('Y-m-d',NOW_TIME), ['class'=>'datepicker'], 'col-md-12 col-lg-6');?>
            <?php echo admin_form_input('title', null, null, 'col-lg-12');?>
            <?php //echo admin_form_checkbox('disp_place[]', NewsConf::$place_options, null, ['inline'=>true, 'empty'=>false], 'col-md-12');?>
            <?php echo admin_form_dropdown('category_id', NewsConf::$category_options, null, ['inline'=>true, 'empty'=>true], 'col-md-12');?>
            <?php echo admin_form_checkbox('flg_important', [1=>'重要なお知らせとして表示'], null, ['inline'=>true, 'notice'=>'重要なお知らせにチェックをつけると、トップページの上部に表示されます。'])?>
            <?php echo admin_form_radio('link_type', NewsConf::$link_type_options, null, ['inline'=>true, 'empty'=>false], 'col-md-12');?>
            <?php echo admin_form_input('external_url', null, ['wrap_class'=>'link_type_'.NEWS_LINK_TYPE_URL.' link_type', 'required'=>true], 'col-lg-12');?>
            <?php echo admin_form_attach_file('attach_path', null, ['data-uploader-dir'=>'/news/attach', 'wrap_class'=>'link_type_'.NEWS_LINK_TYPE_ATTACH.' link_type', 'required'=>true], 'col-lg-12');?>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
</div>
<!-- //.edit-box -->

<div class="edit-box <?php echo !_is_validated('content_html')?'error':'' ?> link_type_<?php echo NEWS_LINK_TYPE_CONTENT?> link_type">
    <h3>本文設定 <?php echo admin_required_badge() ?></h3>
    <?php echo admin_form_article_editor_raw(
        'content_html',
        null,
        [
            'data-inline-ve'=>1,
            'data-types'=>"['big-heading', 'mid-heading', 'small-heading', 'text', 'image', 'link', 'table']",
            'data-big-heading-tag'=>'h3',
            'data-mid-heading-tag'=>'h4',
            'data-small-heading-tag'=>'h5',
            'data-uploader-dir'=>'/news/content'
        ]
    );?>

</div>

<div class="edit-box">
    <h3>ページ設定</h3>
    <p class="small mb-1">デフォルトのmeta情報以外を設定したい場合のみ記入してください。</p>
    <table class="table table-bordered dataTable">
        <tbody>
            <?php echo admin_form_input('meta_title', null, [], 'col-md-12');?>
            <?php echo admin_form_input('meta_description', null, null, 'col-md-12');?>
        </tbody>
    </table>
</div>

<?php include_once VIEWPATH.'admin/common/publish_block.php'; ?>

<?php
if (empty($confirm_flag)) echo admin_input_buttons();
else echo admin_confirm_buttons();
?>

<script>
// リンク種類による入力フィールドの切り替え
var $all_elms = $('.link_type');
var post_link_type = '<?php echo !empty($_POST['link_type']) ? $_POST['link_type'] : '' ?>';

// 入力フォーム用
$('input[name="link_type"]').bind('change', function(){
    var val = $(this).val();
    show_link_type(val);
});
function show_link_type(type){
    $all_elms.hide();
    $('.link_type_'+type).show();
}
show_link_type(post_link_type);
</script>
