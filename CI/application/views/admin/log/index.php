<?php /* 検索結果テンプレート */ ?>
<?php extract($data);?>
<section class="content-header">
    <div class="row">
        <div class="col-lg-7">
            <h2>
                <span><?php echo admin_view_icon(); ?> <?php echo h($entity_name)?> <small>一覧</small></span>
            </h2>
        </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="list-box clearfix">

        <div class="search-box clearfix">

            <div id="conditions">

                <?php echo form_open_multipart($this_url, ['novalidate'=>'novalidate', 'method'=>'get']); ?>

                <?php echo admin_search_row(
                    admin_form_input_raw('keyword'),
                    'キーワード',
                    ['notice'=>'データ種類、操作名、詳細が検索対象となります'],
                    'col-md-12 col-lg-8'); ?>

                <!-- 詳細検索 -->
                <div class="detail-search">
                    <a href="#detail-search-body" data-toggle="collapse"><i class="fas fa-plus-circle"></i> 検索条件をより細かく設定する</a>
                    <div class="detail-search-body collapse" id="detail-search-body">

                        <?php echo admin_search_row(
                        admin_form_radio_raw('flg_success', [1=>'成功', 0=>'失敗'], null, ['empty'=>'指定なし',]),
                        '結果',
                        ['inline'=>true],
                        'col-md-6'); ?>
                    </div>
                </div>

                <?php echo admin_search_buttons(); ?>

                <?php echo form_close(); ?>

            </div><!--//#conditions-->
        </div>
        <!-- //.search-box -->
    </div><!-- //.list-box -->

    <div class="list-box clearfix">
        <?php if (!empty($results)): // データあり?>

        <div class="search-controller">
            <?php echo admin_pagination($pager, ['limits'=>$search_limits, 'pager_enabled'=>false]) ?>
        </div>

        <?php
        // テーブルをレスポンシブ対応にしない場合、
        // class="table-responsive"を削除すればテーブルは画面幅100%（スクロールバーは常に出ない）で設定されます。
        // また、レスポンシブ対応にするの場合のテーブルの最低幅については
        // ipadの縦表示でスクロールバーが出ないように最適化されています(テーブル幅は690px程度)
        // もし最低幅を指定したい場合、tableタグに対し、style="min-width: xxxpx" を設定してください。
        // ※thに設定するwidthに依っては、table全体の幅がmin-widthで設定した幅よりも大きくなる場合があります。
        // どうしてもipadサイズでスクロールバーを出したくない場合は列を削除するか、thの幅を調整するかする必要あり。
        ?>
        <div class="table-responsive reverse-scroll">
            <table class="table table-bordered dataTable" style="min-width:45rem;">
                <thead>
                    <tr>
                        <th class="sorting" style="width:7.5rem"><?php echo sort_link('created', '操作日時'); ?></th>
                        <th class="sorting" style="width:9rem"><?php echo sort_link('login_name', 'ログイン名'); ?> / <br>IPアドレス</th>
                        <th class="sorting" style="width:5.5rem"><?php echo sort_link('class_name', 'データ種類'); ?></th>
                        <th class="sorting" style="width:6.5rem"><?php echo sort_link('action', '操作名'); ?></th>
                        <th class="sorting" style="width:3.5rem"><?php echo sort_link('flg_success', '結果'); ?></th>
                        <th class="sorting" style="">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $v):?>
                    <tr>
                        <td><?php echo h(date('Y-m-d H:i:s', strtotime($v['created'])));?></td>
                        <td class="text-left"><?php echo $v['login_name'] ? h($v['login_name']) : '<span class="text-secondary">未指定</span>' ?><br>(<?php echo h($v['remote_ip']) ?>)</td>
                        <td><?php echo h($v['entity_name']) ?></td>
                        <td><?php echo h($v['action']) ?></td>
                        <td><?php echo $v['flg_success'] ? '成功' : '<span class="text-danger">失敗</span>' ?></td>
                        <td class="text-left"><?php echo nl2br(h($v['description'] ?? ''))?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
                <tfoot>

                </tfoot>
            </table>
        </div><!--//.table-responsive-->

        <div class="search-controller full">
            <?php echo admin_pagination($pager, ['limits'=>$search_limits]) ?>
        </div>

        <?php else: ?>
            <div class="alert alert-info" role="alert">
                データが見つかりませんでした。
            </div>
        <?php endif?>

    </div>
    <!--//.list-box -->

</section>
<!-- /.content -->
