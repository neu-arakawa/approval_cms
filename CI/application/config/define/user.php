<?php
// ユーザ関連定義

define('USER_ROLE_ADMIN', 1);
define('USER_ROLE_APPROVER', 2);
define('USER_ROLE_AUTHOR', 3);

class UserConf
{
    public static $role_options = [
        USER_ROLE_ADMIN     => '管理者',
        USER_ROLE_APPROVER  => '承認者',
        USER_ROLE_AUTHOR    => '投稿者',
    ];
}
