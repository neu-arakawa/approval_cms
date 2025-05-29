<?php /* 編集画面共通テンプレート */ ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <?php if (!empty($sub_title)):?>
    <div class="row mb-1">
        <?php echo $sub_title?>
    </div>
    <?php endif?>
    <div class="row">
        <div>
            <h2>
                <span><?php echo admin_view_icon(); ?> <?php echo h($entity_name)?> <small><?php echo empty($model_id)?'新規登録':'編集'?></small></span>
            </h2>
        </div>
    </div>
</section>

<?php echo admin_validation_errors(); // バリデーションエラーの表示?>

<!-- Main content -->
<section class="content">
    <!-- form start -->
    <?php echo form_open_multipart($this_url, ['novalidate'=>'novalidate']); ?>

    <?php include_once($form_path); // コントローラ別のフォーム部分をインクルード?>

    <?php echo form_close(); ?>

</section>
<!-- /.content -->
