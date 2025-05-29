<?php
require_once substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME'])) . '/common/includes/init.php';
$bodyID = 'noTopicPath';
//$description = '';
$title = '新着情報';
$ex_file_css = array();
$ex_file_js = array();
$topicpath = array(
  'トップ' => '/',
  $title
);
?>
<?php include 'header.php'; ?>

<main id="main">
  <article class="articleCol -single">

    <!-- ////////////////////////////// articleCol__body START -->
    <div class="articleCol__body">
      <section class="section cmn">
        <h1 class="articleSingleTitle"><span>新着情報</span></h1>


        <ul class="newsCategory">
          <li class="newsCategory__item<?php if(empty($selected_cateogry_id)) eh(' -active'); ?>"><a href="/news/">総合新着</a></li>
          <li class="newsCategory__item<?php if($selected_cateogry_id == 'visitors') eh(' -active'); ?>"><a href="/news/visitors">来院される方へ</a></li>
          <li class="newsCategory__item<?php if($selected_cateogry_id == 'medical') eh(' -active'); ?>"><a href="/news/medical">医療関係の方へ</a></li>
        </ul>


        <ul class="newsList -typeA">
          <?php foreach ($results as $k => $v) : ?>
          <li class="newsList__item">
            <p class="newsList__date"><?php eh(dateA($v['disp_date'])); ?></p>
            <p class="newsList__category">
              <span><?php eh(NewsConf::$category_options[$v['category_id']]); ?></span>
            </p>
            <p class="newsList__text">
              <?php if (!empty($v['detail_url'])) : ?>
                <a href="<?php eh($v['detail_url']); ?>" <?php echo($v['detail_target']); ?> <?php echo($v['pointer_events']); ?>>
              <?php endif ;  ?>
              <?php eh($v['title']); ?>
              <?php if (!empty($v['detail_url'])) : ?>
                </a>
              <?php endif ;  ?>
            </p>
          </li>
          <?php endforeach ; ?>
        </ul>


        <?php echo pagination($pager); ?>


      </section>

    </div>
    <!-- ////////////////////////////// articleCol__body END -->


  </article>
</main>
<?php include 'footer.php'; ?>
