<?php
require_once substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME'])) . '/common/includes/init.php';
//$bodyID = '';
$description = h($data['meta_description']);
$title = !empty($data['meta_title']) ? h($data['meta_title']) : h($data['title']);
$ex_file_css = array();
$ex_file_js = array();
$topicpath = array(
    'トップ' => '/',
    '新着情報' => '/news/',
    $title
);
?>
<?php include 'header.php'; ?>

<main id="main" class="newsDetail">
  <article class="articleCol -single -singleSmall">

    <!-- ////////////////////////////// articleCol__body START -->
    <div class="articleCol__body">
      <section class="section cmn">

        <div class="titleSubTypeA">
          <p class="newsList__date"><?php eh(dateA($data['disp_date'])); ?></p>
          <p class="newsList__category">
            <span><?php eh(NewsConf::$category_options[$data['category_id']]); ?></span>
          </p>
        </div>

        <h1><span class="-single"><?php eh($data['title']); ?></span></h1>


        <!-- ////////////////////////////// ve START -->
        <?php echo $data['content_html'] ?>
        <!-- ////////////////////////////// ve END -->


        <div class="pager">
          <ul class="pager__list -typeA">
            <li class="back"><a href="<?php echo hbLink('/news/'); ?>"><span>一覧に戻る</span></a></li>
          </ul>
        </div>

      </section>

    </div>
    <!-- ////////////////////////////// articleCol__body END -->


  </article>
</main>
<?php include 'footer.php'; ?>
