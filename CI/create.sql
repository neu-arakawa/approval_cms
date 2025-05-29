# テーブルのダンプ admin_users
# ------------------------------------------------------------

CREATE TABLE `admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'ログイン名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT 'パスワード',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email',
  `name` varchar(255) DEFAULT NULL COMMENT 'ユーザ氏名',
  `flg_admin` tinyint(4) NOT NULL COMMENT '1:管理者 0:一般',
  `access_token` varchar(255) DEFAULT NULL COMMENT 'パスワードリセット用トークン',
  `token_expires` datetime DEFAULT NULL COMMENT 'リセットトークンの有効期限',
  `retry_count` smallint(6) DEFAULT NULL COMMENT 'ログインの試行回数',
  `retry_failed` datetime DEFAULT NULL COMMENT 'リトライの失敗日時',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


# テーブルのダンプ columns
# ------------------------------------------------------------

CREATE TABLE `columns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` text COMMENT 'タイトル',
  `category_id` smallint(6) DEFAULT NULL COMMENT 'カテゴリID',
  `disp_date` date DEFAULT NULL COMMENT '表示日付',
  `lead` text COMMENT 'リード文',
  `thumb_image_path` text COMMENT 'サムネイル画像パス',
  `content_html` mediumtext COMMENT '本文HTML',
  `search_text` text COMMENT '検索キーワード',
  `meta_keywords` text COMMENT 'meta keywords',
  `meta_description` text COMMENT 'meta description',
  `start_date` datetime DEFAULT NULL COMMENT '公開開始日時',
  `end_date` datetime DEFAULT NULL COMMENT '公開終了日時',
  `flg_publish` tinyint(4) DEFAULT NULL COMMENT '1:公開 0:下書き',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `_test_wysiwyg` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# テーブルのダンプ logs
# ------------------------------------------------------------

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '操作したユーザID',
  `login_name` varchar(255) DEFAULT NULL COMMENT '操作したユーザログイン名',
  `class_name` varchar(255) DEFAULT NULL COMMENT 'コントローラ名',
  `method_name` varchar(255) DEFAULT NULL COMMENT 'メソッド名',
  `data_id` int(11) DEFAULT NULL COMMENT '操作対象ID',
  `entity_name` text COMMENT 'データの通称',
  `action` varchar(255) DEFAULT NULL COMMENT '操作名',
  `description` text COMMENT '詳細',
  `flg_success` tinyint(6) DEFAULT NULL COMMENT '処理結果 1:成功 0:失敗',
  `remote_ip` text COMMENT 'リモートIP',
  `remote_ua` text COMMENT 'リモートUserAgent',
  `created` datetime DEFAULT NULL COMMENT '	',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ログ';



# テーブルのダンプ news
# ------------------------------------------------------------

CREATE TABLE `news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` text COMMENT 'タイトル',
  `disp_date` date DEFAULT NULL COMMENT '表示日付',
  `category_id` smallint(6) DEFAULT NULL COMMENT 'カテゴリID',
  `link_type` smallint(6) DEFAULT NULL COMMENT 'リンク種類 1:本文 2:外部URL 3:添付 4:なし',
  `content_html` mediumtext COMMENT '本文',
  `external_url` text COMMENT '外部URL',
  `attach_path` text COMMENT '添付ファイルパス',
  `meta_keywords` text COMMENT 'meta keywords',
  `meta_description` text COMMENT 'meta description',
  `start_date` datetime DEFAULT NULL COMMENT '掲載期間開始日時',
  `end_date` datetime DEFAULT NULL COMMENT '掲載期間終了日時',
  `flg_publish` tinyint(4) DEFAULT NULL COMMENT '1:公開 2:下書き',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# テーブルのダンプ related_columns
# ------------------------------------------------------------

CREATE TABLE `related_columns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `column_id` int(11) NOT NULL,
  `related_column_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

