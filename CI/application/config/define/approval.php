<?php

define('APPROVAL_STATUS_DRAFT', 1);
define('APPROVAL_STATUS_PENDING', 2);
define('APPROVAL_STATUS_PUBLISHED', 3 );
define('APPROVAL_STATUS_REJECT', 4);
define('APPROVAL_STATUS_PRIVATE', 5);

class ApprovalConf
{
    public static $status_options = [
        APPROVAL_STATUS_DRAFT       => '下書き',
        APPROVAL_STATUS_PENDING     => '承認依頼中',
        APPROVAL_STATUS_PUBLISHED   => '公開',
        APPROVAL_STATUS_PRIVATE     => '公開取下げ',
        APPROVAL_STATUS_REJECT      => '差し戻し'
    ];

    public static $cur_pending_options = [
        APPROVAL_STATUS_PENDING     => '承認依頼中',
        APPROVAL_STATUS_PUBLISHED   => '公開',
        APPROVAL_STATUS_REJECT      => '差し戻し'
    ];

    public static $author_status_options = [
        APPROVAL_STATUS_DRAFT       => '下書き',
        APPROVAL_STATUS_PENDING     => '承認依頼'
    ];

    public static $admin_status_options = [
        APPROVAL_STATUS_DRAFT       => '下書き',
        APPROVAL_STATUS_PENDING     => '承認依頼'
    ];
}
