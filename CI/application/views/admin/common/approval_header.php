<?php if(!empty($current_data)){  ?>
	<?php if(!empty($current_data['published'])){  ?>
    <div class="edit-box -published">この記事は公開中です。</div>
	<?php } else if(!empty($current_data['future'])){  ?>
    <div class="edit-box -published">この記事は <span class='h2'><?php echo $current_data['start_date']  ?></span> に公開します。</div>
	<?php } else{ ?>
    <div class="edit-box -not_published">この記事は公開していません。[ステータス: <?php echo opt($current_data['status'], ApprovalConf::$status_options ) ?>]</div>
	<?php } ?>
<?php } ?>

