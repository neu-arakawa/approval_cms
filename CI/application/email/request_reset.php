From: 株式会社●●  <test123@n-e-u.co.jp>
To: <?php em($email)?>
Reply-to: test123@n-e-u.co.jp
Subject: パスワード再発行用のURLのご案内

パスワード再発行用のURLをご案内いたします。
下記URLにアクセスしていただき、新しいパスワードを設定してください。

<?php em($reset_url)?>

※ パスワード再発行用URLの有効期限は<?php echo PASSWORD_RESET_EXPIRES?>分となります。
　アクセスできない場合は再度パスワード再発行のお手続きをお願いします。
