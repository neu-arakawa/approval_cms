<section class="content-header">
    <div class="row">
        <div>
            <h2>
                <span><?php echo admin_view_icon(); ?><?php echo h($entity_name)?> <small>一覧</small></span>
            </h2>
            <?php if (!empty($sub_title)):?>
                <h3><?php echo $sub_title?></h3>
            <?php endif?>
        </div>
        <a href="<?php echo admin_base_url("/{$controller}/add") ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> 新規登録</a>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <?php
    extract($data);
    include_once($index_path); // コントローラ別の検索結果部分をインクルード
    ?>
</section>
<!-- /.content -->