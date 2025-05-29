<?php
// ユーザ関連定義

define('USER_FLG_ADMIN_ADMIN','1');
define('USER_FLG_ADMIN_GENERAL', '0');

define('USER_PERM_NEWS', 1);

class UserConf
{
    public static $flg_admin_options = [
        USER_FLG_ADMIN_GENERAL => '一般',
        USER_FLG_ADMIN_ADMIN => '管理者',
    ];

    public static $perm_options = [
        USER_PERM_NEWS => '新着情報管理',
    ];
}
