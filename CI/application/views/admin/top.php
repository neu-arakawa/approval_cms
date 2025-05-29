<section class="content-header">
  <div class="row">
      <div class="col-lg-7">
          <h2><i class="fas fa-tachometer-alt"></i> ダッシュボード</h2>
      </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="list-box">
      <h3>最近の操作履歴</h3>

      <?php if (!empty($logs)): // データあり?>

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
                      <th class="sorting" style="width:8rem">操作日時</th>
                      <th class="sorting" style="width:10rem">ログイン名 / <br>IPアドレス</th>
                      <th class="sorting" style="width:6.5rem">データ種類</th>
                      <th class="sorting" style="width:6rem">操作名</th>
                      <th class="sorting" style="width:4rem">結果</th>
                      <th class="sorting" style="">詳細</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach($logs as $v):?>
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
        <?php 
        $user = $this->my_session->admin;
        if( $user->flg_admin ){
        ?>
        <div class="mt-2"><a href="<?php echo admin_base_url('/log') ?>" class="ml-1 text-sm"><i class="fas fa-angle-right"></i> もっと見る</a></div>
        <?php } ?>

      <?php else: ?>
          <div class="alert alert-info" role="alert">
              データが見つかりませんでした。
          </div>
      <?php endif?>

  </div>
  <!-- //.edit-box -->
</section>
<!-- /.content -->
