<?php

define('NEWS_CATEGORY_NEWS', 1);
define('NEWS_CATEGORY_OUTPATIENT', 2);
define('NEWS_CATEGORY_INPATIENT', 3);
define('NEWS_CATEGORY_ABOUT_HOSPITAL', 4);
define('NEWS_CATEGORY_MEDICAL', 5);
define('NEWS_CATEGORY_FACILIT', 6);
define('NEWS_CATEGORY_FOR_VISITORS', 7);
define('NEWS_CATEGORY_EVENT', 8);
define('NEWS_CATEGORY_TRAINING_SEMINAR', 9);
define('NEWS_CATEGORY_RECRUIT', 10);
define('NEWS_CATEGORY_FOR_MEDICAL', 11);


define('NEWS_LINK_TYPE_ATTACH', 1);
define('NEWS_LINK_TYPE_URL', 2);
define('NEWS_LINK_TYPE_CONTENT', 3);
define('NEWS_LINK_TYPE_NONE', 4);

class NewsConf
{
    public static $category_options = [
        NEWS_CATEGORY_NEWS              =>   'ニュース',
        NEWS_CATEGORY_OUTPATIENT        =>   '外来のご案内',
        NEWS_CATEGORY_INPATIENT         =>   '入院のご案内',
        NEWS_CATEGORY_ABOUT_HOSPITAL    =>   '当院について',
        NEWS_CATEGORY_MEDICAL           =>   '診療情報',
        NEWS_CATEGORY_FACILIT           =>   '施設情報',
        NEWS_CATEGORY_FOR_VISITORS      =>   '来院される方へ',
        NEWS_CATEGORY_EVENT             =>   'イベント・セミナー',
        NEWS_CATEGORY_TRAINING_SEMINAR  =>   '研修等（医療関係者）',
        NEWS_CATEGORY_RECRUIT           =>   '採用情報',
        NEWS_CATEGORY_FOR_MEDICAL       =>   '医療関係の方へ',
    ];

    public static $link_type_options = [
        NEWS_LINK_TYPE_ATTACH => '添付ファイル',
        NEWS_LINK_TYPE_URL => 'URL',
        NEWS_LINK_TYPE_CONTENT => '本文（詳細ページ作成）',
        NEWS_LINK_TYPE_NONE => 'なし',
    ];

}
