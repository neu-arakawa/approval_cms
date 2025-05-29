ALTER TABLE `schedule_doctors` ADD `sort_no` SMALLINT NULL COMMENT '同一枠内でのソート番号' AFTER `comment`;

CREATE TABLE `schedule_months` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `month` varchar(255) NOT NULL DEFAULT '' COMMENT '担当医表の年月',
  `publish_day` smallint(6) NOT NULL COMMENT '公開する日（担当医表の年月の前月）',
  `publish_time` time NOT NULL COMMENT '公開時刻',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `admin_users` ADD `flg_acl` TEXT NULL COMMENT '管理項目へのアクセスID権限リスト（複数の場合はカンマ区切り）' AFTER `flg_admin`;
ALTER TABLE `admin_users` CHANGE `flg_acl` `flg_acl` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '管理項目へのアクセスID権限リスト（複数の場合はカンマ区切り）';
