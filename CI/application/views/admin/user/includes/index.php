<?php /* 検索結果テンプレート */ ?>
<div class="list-box clearfix">

    <div class="search-box clearfix">

        <div id="conditions">

            <?php echo form_open_multipart($this_url, ['novalidate'=>'novalidate', 'method'=>'get']); ?>

            <?php echo admin_search_row(
                admin_form_input_raw('keyword'),
                'keyword',
                null,
                'col-md-12 col-lg-8'); ?>

                <!-- 詳細検索 -->
                <div class="detail-search">
                    <a href="#detail-search-body" data-toggle="collapse"><i class="fas fa-plus-circle"></i> 検索条件をより細かく設定する</a>
                    <div class="detail-search-body collapse" id="detail-search-body">
                        <?php 
                        echo admin_search_row(
                            admin_form_dropdown_raw('role_id', UserConf::$role_options, null, ['empty'=>'指定なし',]),
                            lang('role_id'),
                            null,
                            'col-md-2'
                        ); ?>
                    </div>
                </div>

            <?php echo admin_search_buttons(); ?>

            <?php echo form_close(); ?>

        </div><!--// #conditions -->

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
        <table class="table table-bordered dataTable" style="min-width: 52rem">
        <colgroup>
          <col style="width: 4rem;">
          <col style="width: 10rem;">
          <col style="width: 10rem;">
          <col style="width: 10rem;">
          <col style="width: auto;">
          <?php if (AUTH_RETRY_MAX>=0):// ログイン試行の設定あり?>
          <col style="width: 8rem;">
        <?php endif?>
          <col style="width: 12rem;">
        </colgroup>
            <thead>
                <tr>
                    <th class="sorting"><?php echo sort_link('id', 'ID'); ?></th>
                    <th class="sorting text-left"><?php echo sort_link('name', 'name'); ?></th>
                    <th class="sorting text-left"><?php echo sort_link(AUTH_USER_FIELD, AUTH_USER_FIELD); ?></th>
                    <th class="sorting"><?php echo lang('role_id'); ?></th>
                    <th class="sorting text-left"><?php echo sort_link(AUTH_EMAIL_FIELD, 'email'); ?></th>
                    <?php if (AUTH_RETRY_MAX>=0):// ログイン試行の設定あり?>
                        <th class="sorting">アカウント<br>ロック <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="ログインを連続で失敗し、ロックされたアカウントには「ロック中」のラベルが表示されます。"></i></th>
                    <?php endif?>
                    <th class="sorting">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($results as $v):?>
                <tr>
                    <td><?php echo h($v['id']); ?></td>
                    <td class="text-left"><?php echo h($v['name']) ?></td>
                    <td class="text-left"><?php echo h($v[AUTH_USER_FIELD]) ?></td>
                    <td><?php echo opt($v['role_id'], UserConf::$role_options) ?></td>
                    <td class="text-left"><?php echo h($v[AUTH_EMAIL_FIELD],'(未設定)') ?></td>
                    <?php if (AUTH_RETRY_MAX>=0):// ログイン試行の設定あり?>
                        <td><?php
                        if ($v['retry_count'] >= AUTH_RETRY_MAX) { // アカウントロック状態
                            $msg = 'アカウント('.h($v[AUTH_USER_FIELD]).')のロックを解除します。よろしいですか？';
                            $url = admin_base_url('user/unlock/'.$v['id']);
                            echo '<a href="'.$url.'" onclick="if(!confirm(\''.$msg.'\')) return false;" class="badge badge-secondary"><i class="fas fa-lock"></i> ロック中</a>';
                        }
                        ?></td>
                    <?php endif?>
                    <td><?php echo admin_list_menu($v['id'], ['replicate'=>false])?></td>
                </tr>
                <?php endforeach;?>
            </tbody>

        </table>
    </div><!--//.table-responsive-->

    <div class="search-controller full">
        <?php echo admin_pagination($pager, ['limits'=>$search_limits, 'pager_enabled'=>false]) ?>
    </div>

    <?php else: ?>
        <div class="alert alert-info" role="alert">
            データが見つかりませんでした。
        </div>
    <?php endif?>

</div>
<!--//.list-box -->
