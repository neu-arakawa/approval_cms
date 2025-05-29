<?php
// ページネーション
function pagination($pager, $options=[])
{
    $default = [
        'limits' => [],
        'limit_enabled' => true,
        'pager_enabled' => true,
        'disp_enabled' => true,
    ];
    $options = array_merge($default, $options);

    unset($pager['order_by']);

    // 必要な変数が未定義の場合
    if (empty($pager) || empty($pager['count'])) {
        return;
    }
    if (empty($pager['offset'])) {
        $offset = 0;
    }

    // 何件目を表示しているかの数字
    if ($pager['limit'] === null) { // 全件表示
        $first = 1;
        $last = $pager['count'];
    } else {
        $first = $pager['offset'] + 1;
        $last = $pager['offset'] + $pager['limit'];
        if ($last > $pager['count']) {
            $last = $pager['count'];
        }
    }

    // 表示反映のため、GET/POSTに設定
    $_GET['limit'] = $_POST['limit'] = $pager['limit'];
    if (!empty($pager['order_by'])) {
        $_GET['direction'] = $_POST['direction'] = reset($pager['order_by']);
        $_GET['sort'] = $_POST['sort'] = key($pager['order_by']);
    }

    // paginationの設定
    $config = [];
    $config['total_rows'] = $pager['count'];
    $config['per_page'] = $pager['limit'];

    $config['page_query_string'] = true;
    $config['query_string_segment'] = 'offset';
    $config['reuse_query_string'] = true;

    $config['first_link'] = false;
    $config['last_link'] = false;

    $config['num_links'] = 2;

    $config['next_link'] = '';
    $config['next_tag_open'] = '<li class="next">';
    $config['next_tag_close'] = '</li>';
    $config['prev_tag_open'] = '<li class="prev">';
    $config['prev_tag_close'] = '</li>';

    $config['cur_tag_open'] = '<li class="current">';
    $config['cur_tag_close'] = '</li>';

    $config['prev_link'] = '';
    $config['num_tag_open'] = '<li>';
    $config['num_tag_close'] = '</li>';

    if (!empty($pager['config'])) {
        $config = array_merge($config, $pager['config']);
    }

    $CI = get_instance();
    $CI->pagination->initialize($config);

    $out = '';

    if ($options['pager_enabled']) {
        $out .= '<div class="pager">';
        $out .= '<ul class="pager__list">';
        $out .= $CI->pagination->create_links();
        $out .= '</ul>';
        $out .= '</div>';
    }

    return $out;
}
