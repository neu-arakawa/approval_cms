<?php
class Admin_news_model extends MY_Model
{
    protected $_table = 'news';
    protected $_title_field = 'title';          // 見出しとして使用するフィールド名

    // limitを設定すると、[20件 50件 100件 全件]などの
    // ユーザによるlimit選択機能が使用できなくなるので注意
    protected $_search_options = [
        'order_by' => ['modified'=>'DESC', 'id'=>'DESC'],
    ];
    protected $_search_fields = [ // 検索処理用の定義
        'keyword' => ['type'=>'query', 'method'=>'_search_keyword', 'field'=>['title','content_html']],
        'published' => ['type'=>'query', 'method'=>'_search_published_for_news'],
        'draft' => ['type'=>'query', 'method'=>'_search_draft'],
        'category_id' => ['type'=>'value']
    ];
    protected $_search_sorts = [ // 検索用ソートキー定義
        'id' => ['id'=>'ASC'],
        'disp_date' => ['disp_date'=>'ASC'],
        'title' => ['title'=>'ASC', 'modified'=>'DESC'],
        'category_id' => ['category_id'=>'ASC', 'modified'=>'DESC'],
    ];

    protected $_uploader_model = 'news';
    protected $_uploader_fields = [
        'content_html', 'attach_path',
    ];

    // _after_findフィルター実行時に
    // データレコード別に呼び出される
    protected function _append_data($data)
    {
        $data = parent::_append_data($data);    // デフォルトの付加情報追加
        $data['detail_url'] = base_url('/news/detail/'.$data['id']); // 詳細画面URLの追加
        // $data['disp_place'] = explode(',', $data['disp_place']);
        
        return $data;
    }

    protected function _before_save($data, $id=null, $raw=[], $context='')
    {
        $data = parent::_before_save($data, $id, $raw, $context);
        if (!empty($data['link_type'])) {
            switch ($data['link_type']) {
                case NEWS_LINK_TYPE_ATTACH:
                    $data['content_html'] = null;
                    $data['external_url'] = null;

                    // ファイルサイズの取得
                    if (!empty($data['attach_path'])) {
                        $fpath = DOCUMENT_ROOT. $data['attach_path'];
                        if (file_exists($fpath)) {
                            $data['attach_size'] = filesize($fpath);
                        } else {
                            $data['attach_size'] = 0;
                        }
                    }
                    break;
                case NEWS_LINK_TYPE_URL:
                    $data['attach_path'] = null;
                    $data['attach_size'] = null;
                    $data['content_html'] = null;
                    break;
                case NEWS_LINK_TYPE_CONTENT:
                    $data['attach_path'] = null;
                    $data['attach_size'] = null;
                    $data['external_url'] = null;
                    break;
                default:
                    $data['attach_path'] = null;
                    $data['attach_size'] = null;
                    $data['external_url'] = null;
                    $data['content_html'] = null;
            }
        }
        // 掲載場所をカンマ区切りにする
        if (!empty($data['disp_place']) && is_array($data['disp_place'])) {
            $data['disp_place'] = implode(',', array_filter($data['disp_place']));
        }

        return $data;
    }

    protected function _after_save($data, $id, $raw=[], $context='')
    {
        $data = parent::_after_save($data, $id, $raw, $context);

        if($context === 'temporary'){
            $search_text = [];
            $search_text[] = $raw['title'];
            $search_text[] = $raw['content_html'];
            $this->db->update($this->_table, [
                'search_text' => implode("\n", $search_text)
            ], ['id' => $id]);
        }
        return true;
    }


    // 検索処理用
    // キーワード検索
    // $data: 入力データ
    protected function _search_published_for_news($data)
    {
        $now = date('Y-m-d H:i:s', NOW_TIME);
        $last_year = date('Y-m-d', strtotime('-1 year', strtotime(NOW)));
        $cond = "
                flg_publish=1 and
                (
                    publish_term=0 or
                    ( 
                        publish_term=1 AND (
                            (start_date IS NULL AND end_date >= '{$now}') OR
                            (start_date <= '{$now}' AND end_date IS NULL) OR
                            (start_date <= '{$now}' AND end_date >= '{$now}')
                        )
                    ) or 
                    (
                        publish_term=2 AND 
                        disp_date >= '{$last_year}'
                    )
                ) ";
        if (empty($data)) {
            $cond = "NOT ($cond)";
        }
        return $cond;
    }
}
