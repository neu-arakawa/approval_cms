<?php if(!empty($is_login)): // ログイン?>

<?php elseif(!empty($is_modal)): // ログイン?>
    </main>
<?php else: // その他のページ?>
    </main>

    <footer class="clearfix mt-3">
      <a href="#" class="btn pagetop btn-secondary float-right text-light mr-3"><i class="fas fa-arrow-up" aria-hidden="true"></i> ページトップ</a>
    </footer>
    </div>
<?php endif?>
  </body>
</html>