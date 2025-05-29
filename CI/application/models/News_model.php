<?php
class News_model extends MY_Model
{
    protected $_table = 'news';
    protected $_search_options = [
        'order_by' => ['disp_date'=>'DESC', 'modified'=>'DESC'],
        'where' => [],
        'limit' => 20,
    ];

    public function __construct()
    {
        parent::__construct();

        $now = date('Y-m-d H:i:s', NOW_TIME);
        $last_year = date('Y-m-d', strtotime('-1 year', strtotime(NOW)));
        $this->_search_options = [
            'order_by' => ['disp_date'=>'DESC', 'modified'=>'DESC'],
            'where' => [
                'flg_publish' => 1,
                "(
                    (start_date IS NULL AND end_date IS NULL) OR
                    (start_date <= '".NOW."' AND end_date >= '".NOW."') OR
                    (start_date IS NULL AND end_date >= '".NOW."') OR
                    (start_date <= '".NOW."' AND end_date IS NULL)
                )",
            ],
            'limit' => 20,
        ];
    }

    protected function _append_data($data)
    {
        $data = parent::_append_data($data);

        if (!empty($data['link_type'])) {
            switch ($data['link_type']) {
                case NEWS_LINK_TYPE_ATTACH:
                    $data['detail_url'] = $data['attach_path'];
                    $data['detail_target'] = 'target="_blank" rel="noopener"';
                    $data['pointer_events'] = '';
                    break;
                case NEWS_LINK_TYPE_URL:
                    $data['detail_url'] = $data['external_url'];
                    $data['detail_target'] = 'target="_blank" rel="noopener"';
                    $data['pointer_events'] = '';
                    break;
                case NEWS_LINK_TYPE_CONTENT:
                    $data['detail_url'] = base_url('/news/detail/'.$data['id']);
                    $data['detail_target'] = '';
                    $data['pointer_events'] = '';
                    break;
                default:
                    $data['detail_url'] = '';
                    $data['detail_target'] = '';
                    $data['pointer_events'] = 'style="pointer-events: none;"';
            }
        }
        return $data;
    }


    public function convert_category_id($category_id = null)
    {
        if (is_null($category_id)) {
            return null;
        }

        $rtn = [$category_id];

        switch ($category_id) {
        case 'visitors':
            $rtn = [
                NEWS_CATEGORY_OUTPATIENT,
                NEWS_CATEGORY_INPATIENT,
                NEWS_CATEGORY_ABOUT_HOSPITAL,
                NEWS_CATEGORY_MEDICAL,
                NEWS_CATEGORY_FACILIT,
                NEWS_CATEGORY_FOR_VISITORS,
                NEWS_CATEGORY_EVENT
            ];
            break;
        case 'medical':
            $rtn = [
                NEWS_CATEGORY_TRAINING_SEMINAR,
                NEWS_CATEGORY_RECRUIT,
                NEWS_CATEGORY_FOR_MEDICAL,
            ];
            break;
        case 'outpatient':
            $rtn = [
                NEWS_CATEGORY_OUTPATIENT,
            ];
            break;
        case 'inpatient':
            $rtn = [
                NEWS_CATEGORY_INPATIENT,
            ];
            break;
        case 'about':
            $rtn = [
                NEWS_CATEGORY_ABOUT_HOSPITAL,
            ];
            break;
        case 'treatment':
            $rtn = [
                NEWS_CATEGORY_MEDICAL,
            ];
            break;
        case 'facility':
            $rtn = [
                NEWS_CATEGORY_FACILIT,
            ];
            break;
        }

        return $rtn;
    }


    public function get_important_list()
    {
        $options = [
            'where' => ['flg_important' => 1],
            'limit' => 3,
        ];
        return $this->search($options);
    }


    public function get_top_list()
    {
        $rtn = [
            'all'      => [],
            'visitors' => [],
            'medical'  => [],
        ];
        foreach ($rtn as $k => $v) {
            if ($k == 'all') {
                $options = [
                    'limit' => 5,
                ];
            } else {
                $options = [
                    'where_in' => ['category_id' => $this->convert_category_id($k)],
                    'limit'    => 5,
                ];
            }
            $rtn[$k] = $this->search($options);
        }
        return $rtn;
    }
}
