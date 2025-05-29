<?php /* 検索結果テンプレート */ ?>
<div class="list-box clearfix">

    <div class="search-box clearfix">

            <div id="conditions">

                <?php echo form_open_multipart($this_url, ['novalidate'=>'novalidate', 'method'=>'get']); ?>

                <?php echo admin_search_row(
                    admin_form_input_raw('keyword', null),
                    'keyword',
                    [
                        'notice'=>lang('title').'、'.lang('content_html').'が検索対象になります',
                    ],
                    'col-md-12 col-lg-8'); ?>

                <!-- 詳細検索 -->
                <div class="detail-search">
                    <a href="#detail-search-body" data-toggle="collapse"><i class="fas fa-plus-circle"></i> 検索条件をより細かく設定する</a>
                    <div class="detail-search-body collapse" id="detail-search-body">


                        <?php 
                        echo admin_search_row(
                            admin_form_dropdown_raw('category_id', NewsConf::$category_options, null, ['empty'=>'指定なし',]),
                            lang('category_id'),
                            null,
                            'col-md-12'
                        ); ?>


                        <?php echo admin_search_row(
                            admin_form_radio_raw('published', ['0'=>'非公開', '1'=>'公開'], null, ['empty'=>'指定なし',]),
                            '公開状態',
                            ['inline'=>true],
                            'col-md-12'); ?>

                        <?php echo admin_search_row(
                            admin_form_checkbox_raw('draft', ['1'=>'公開取下げのみ'], null, null),
                            '',
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
        <?php echo admin_batch_menu()?>
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
        <table class="table table-bordered dataTable" style="">
            <thead>
                <tr>
                    <th class="sorting" style="width:3rem"><input type="checkbox" value="1" class="batch_all" /></th>
                    <th class="sorting" style="width:4rem"><?php echo sort_link('id', 'ID'); ?></th>
                    <th class="sorting" style="">
                        <span class="inline-block"><?php echo sort_link('title', 'title'); ?></span>
                    </th>
                    <th class="sorting" style="width:8rem"><?php echo sort_link('disp_date', 'disp_date'); ?> </th>
                    <th class="sorting" style="width:8rem"><?php echo sort_link('category_id', 'category_id'); ?></th>
                    <th class="sorting" style="width:10rem">公開状態</th>
                    <th class="sorting" style="width:13.3rem">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($results as $v):?>
                <tr class="<?php echo $v['published']?'':'disabled' ?>">
                    <td><input type="checkbox" value="<?php echo h($v['id']);?>" class="batch" /></td>
                    <td class="text-sm"><?php echo h($v['id']); ?></td>
                    <td class="text-left">
                    <?php 
                        if( !empty($v['is_temporary']) ){
                            echo '<div class="badge badge-warning -temporary">一時保存</div>';
                        }
                        echo '<div>';
                        echo h($v['title'],'(未設定)');
                        echo '</div>';
                        if( !empty($v['is_temporary']) ){
                            echo '<a href="'.base_url( $controller.'/preview_page/'. md5($v['id']) ).'" 
                                style="font-size:.6rem" target="_blank"><i class="fas fa-clone small"></i> 一時保存のプレビューを見る</a>';
                        }
                    ?>
                    </td>
                    <td class="text-center">
                        <span class="text-xm"><?php echo !empty($v['disp_date']) ? date('Y-m-d',strtotime($v['disp_date'])) : '(未設定)' ?></span>
                    </td>
                    <td class="text-center">
                        <?php echo h(opt($v['category_id'], NewsConf::$category_options)) ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $help = '';
                        $url = null;

                        if ($v['link_type']==NEWS_LINK_TYPE_ATTACH){
                            $url = $v['attach_path'];
                        } else if ($v['link_type']==NEWS_LINK_TYPE_URL) {
                            $url = $v['external_url'];
                        }

                        if (!empty($v['published'])) { // 公開中
                            if ($v['link_type']==NEWS_LINK_TYPE_CONTENT) {
                                $url = $v['detail_url']; // 詳細画面のURLを表示
                            }
                            $label = '公開中';
                        } else { // 非公開中
                            if ($v['link_type']==NEWS_LINK_TYPE_CONTENT) {
                                $url = admin_preview_url($v['detail_url'], $v['validated']);
                            }
                            $label = '非公開';
                        }
                        ?>
                        <?php if (!empty($url)):?>
                            <a href="<?php echo h($url)?>" target="_blank" class="text-sm">
                            <?php echo h($label)?> <i class="fas fa-external-link-alt"></i>
                            </a>
                        <?php else :?>
                            <?php echo h($label)?>
                        <?php endif?>
                        <?php echo empty($v['flg_publish']) ? '<br><small>（公開取下げ）</small>' : ''; ?>

                    </td>
                    <td>
                        <?php echo admin_list_menu(
                            $v['id'],
                            [
                                // 公開フラグがONなら非表示、そうでなければバリデート済みなら表示、バリデートがまだであれば無効
                                'publish' => !!$v['flg_publish']
                                                ? false
                                                : (!!$v['validated']
                                                    ? true : 'disabled'),
                                // 公開フラグがONなら表示
                                'draft' => !!$v['flg_publish'],
                            ]
                        )?>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
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
