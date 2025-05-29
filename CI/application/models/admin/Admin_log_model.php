<?php

class Admin_log_model extends MY_Model
{
    protected $_table = 'logs';
    protected $_entity_name = '操作履歴';
    protected $_search_options = [
        'order_by' => ['created'=>'DESC', 'login_name'=>'ASC'],
        'limit' => DEFAULT_DB_LIMIT,
    ];
    protected $_search_fields = [ // 検索処理用の定義
        'keyword' => ['type'=>'query', 'method'=>'_search_keyword', 'field'=>['action','description', 'class_name', 'method_name']],
        'flg_success' => ['type'=>'value'],
    ];
    protected $_search_sorts = [ // 検索用ソートキー定義
        'created' => [
            'ASC' => ['created'=>'ASC', 'login_name'=>'ASC'],
            'DESC' => ['created'=>'DESC', 'login_name'=>'ASC'],
        ],
        'login_name' => [
            'ASC' => ['login_name'=>'ASC', 'created'=>'DESC'],
            'DESC' => ['login_name'=>'DESC', 'created'=>'DESC'],
        ],
        'class_name' => [
            'ASC' => ['class_name'=>'ASC', 'created'=>'DESC'],
            'DESC' => ['class_name'=>'DESC', 'created'=>'DESC'],
        ],
        'action' => [
            'ASC' => ['action'=>'ASC', 'created'=>'DESC'],
            'DESC' => ['action'=>'DESC', 'created'=>'DESC'],
        ],
    ];

}
