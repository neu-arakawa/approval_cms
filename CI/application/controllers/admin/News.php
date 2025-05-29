<?php
class News extends Admin_Controller
{
    const ENTITY_NAME = 'お知らせ';
    const VIEW_ICON = '<i class="far fa-newspaper-o"></i>';

    protected $_cms_uploader_model = 'news'; // CMS連携アップローダモデル名（サブディレクトリ名）

    protected $_exec_methods = [                        // 基本操作系メソッドの中で実行を許可するメソッド(この設定はAdminControllerに定義されていないメソッドには影響を与えない)
        'index', 'add', 'edit',
        'delete', 'delete_all',
        'replicate', 'replicate_all',
        'publish', 'publish_all',
        'clear_temporary'
    ];
}
