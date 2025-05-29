<?php

define('NEWS_LINK_TYPE_ATTACH', 1);
define('NEWS_LINK_TYPE_URL', 2);
define('NEWS_LINK_TYPE_CONTENT', 3);
define('NEWS_LINK_TYPE_NONE', 4);

class NewsConf
{
    public static $category_options = [
        1 => 'イベント',
        'お知らせ'
    ];

    public static $link_type_options = [
        NEWS_LINK_TYPE_ATTACH => '添付ファイル',
        NEWS_LINK_TYPE_URL => 'URL',
        NEWS_LINK_TYPE_CONTENT => '本文（詳細ページ作成）',
        NEWS_LINK_TYPE_NONE => 'なし',
    ];

}
