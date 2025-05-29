<?php

// バリデーション定義
// 下書き保存時は定義されたバリデーションのうち、
// [required] [callback__required_any] のルールは
// 取り除いた上で実行されます

$config = [

    // お知らせ編集
    'admin/news/edit' => [
        [
            'field' => 'disp_date',
            'rules' => 'required|valid_date',
        ],
        [
            'field' => 'title',
            'rules' => 'required',
        ],
        [
            'field' => 'category_id',
            'rules' => 'required',
        ],
        [
            'field' => 'link_type',
            'rules' => 'required',
        ],
        [
            'field' => 'content_html',
            'rules' => 'callback__required_if[link_type='.NEWS_LINK_TYPE_CONTENT.']',
        ],
        [
            'field' => 'external_url',
            'rules' => 'callback__required_if[link_type='.NEWS_LINK_TYPE_URL.']',
        ],
        [
            'field' => 'attach_path',
            'rules' => 'callback__required_if[link_type='.NEWS_LINK_TYPE_ATTACH.']',
        ],
        [
            'field' => 'start_date',
            'rules' => 'valid_datetime_minute|valid_datetime_order[end_date]',
        ],
        [
            'field' => 'end_date',
            'rules' => 'valid_datetime_minute',
        ],
    ],

    // ユーザ編集
    'admin/user/edit' => [
        [
            'field' => AUTH_USER_FIELD,
            'rules' => 'required|is_unique_by_id[admin_users.'.AUTH_USER_FIELD.']|min_length[3]',
        ],
        [
            'field' => AUTH_PASSWORD_FIELD,
            'rules' => 'callback__validate_password_required|valid_password',
        ],
        [
            'field' => AUTH_PASSWORD_FIELD.'_confirm',
            'rules' => 'matches['.AUTH_PASSWORD_FIELD.']'
        ],
        [
            'field' => 'name',
            'rules' => 'required',
        ],
        [
            'field' => AUTH_EMAIL_FIELD,
            'rules' => 'valid_email|is_unique_by_id[admin_users.'.AUTH_EMAIL_FIELD.']',
        ]
    ],

    // ユーザ編集
    'admin/auth/reset' => [
        [
            'field' => AUTH_PASSWORD_FIELD,
            'rules' => 'required|valid_password',
        ],
        [
            'field' => AUTH_PASSWORD_FIELD.'_confirm',
            'rules' => 'matches['.AUTH_PASSWORD_FIELD.']'
        ],

    ],
];

$config['admin/user/my_edit'] = $config['admin/user/edit'];

