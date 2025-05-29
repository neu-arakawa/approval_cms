<?php
class Page extends MY_Controller {

    public function display(){
        $url = parse_url($_SERVER['REQUEST_URI']);
        if( $url['path'] !=='/' && !preg_match('/^[a-z0-9._\-\/]+$/i'.(UTF8_ENABLED ? 'u' : ''), $url['path']) )
            show_404();
        $php_file = FCPATH. trim($url['path'], '/');
        if( !preg_match('/\.php$/i',$php_file) )
            $php_file .= '/index.php';
        if( preg_match('/\/simulation\/inquiry\/index\.php/', $php_file) ){
            $this->output->enable_profiler(false);
            return ;
        }
        if (!file_exists($php_file))show_404();
        $url = parse_url($_SERVER['REQUEST_URI']);
        if(empty($url['path'])) show_404();

        $url['path'] = trim($url['path'],'/');

        // お知らせ
        if ($url['path'] === '') {
            $this->load_model(['News']);
            $important_news = $this->News->get_important_list();
            $top_news       = $this->News->get_top_list();
            $this->load->vars(compact('important_news'));
            $this->load->vars(compact('top_news'));

        } elseif (in_array($url['path'], ['outpatient', 'inpatient', 'about', 'treatment', 'facility', 'medical'])) {
            $this->load_model(['News']);
            $options = [
                'limit' => 3,
            ];
            $options['where_in']['category_id'] = $this->News->convert_category_id($url['path']);
            $news = $this->News->search($options);
            $this->load->vars(compact('news'));

        }

        // トピックス
        if ($url['path'] === '') {
            $this->load_model(['Topic']);
            $options = [
                'where'    => ["(FIND_IN_SET('".TOPIC_DISP_PLACE_TOP."', disp_place))"],
                'limit'    => 5,
            ];
            $topics = $this->Topic->search($options);
            $this->load->vars(compact('topics'));
        }

        // 臨床研究一覧
        if( $url['path'] === 'about/research' ){
            $this->load_model(['Clinical_research']);

            $researches = $this->Clinical_research->search([
                'select'   => "research_department_id",
                'group_by' => ["research_department_id"],
                'order_by' => false,
                'limit'    => false
            ], $pager, false,false);
            $research_department_ids =
                array_column($researches, 'research_department_id');

            $options =
                ResearchConf::research_department_options();
            $dirname_options =
                ResearchConf::research_department_dirname_options();

            $research_departments = [];
            foreach($options as $k => $v){
                if( in_array($k, $research_department_ids) ){
                    $research_departments[ $dirname_options[$k] ] = $v;
                }
            }

            $this->load->vars(compact('research_departments'));
        }

        // よくあるご質問
        if(
            $url['path'] === 'outpatient/first_time' ||
            $url['path'] === 'medical/patients_referral' ||
            $url['path'] === 'medical/faq'
        ){
            $where = [];
            if($url['path'] === 'outpatient/first_time'){
                $where = ["(FIND_IN_SET('".FAQ_DISP_PLACE_OUTPATIENT_FIRST."', disp_place))"];
            }
            else if($url['path'] === 'medical/patients_referral'){
                $where = ["(FIND_IN_SET('".FAQ_DISP_PLACE_PATIENTS_REFERRAL."', disp_place))"];
            }
            else if($url['path'] === 'medical/faq'){
                $where = ["(FIND_IN_SET('".FAQ_DISP_PLACE_MEDICAL."', disp_place))"];
            }

            $this->load_model(['Faq']);
            $faqs = $this->Faq->search([
                'where' => $where
            ], $pager, true);

            $this->load->vars(compact('faqs'));
        }

        // セカンドオピニオンのご案内
        if( $url['path'] === 'treatment/second_opinion' ){
            $this->load_model(['Department']);
            $this->Department->relate_names = ['schedule'];
            $departments = $this->Department->search([
                'where' => [
                    'flg_second_opinion' => true
                ]
            ]);

            $this->load->vars(compact('departments'));
        }

        // レジメン
        if( $url['path'] === 'medical/regimen' ){
            $this->load_model(['Regimen']);
            $regimens = $this->Regimen->search();
            $this->load->vars(compact('regimens'));
        }

        // 外来担当医表
        if( $url['path'] === 'treatment/schedule' ){
            $this->load_model(['Department']);
            $this->Department->relate_names = ['schedule'];
            $results = $this->Department->search([
                'where_in' => [
                    'category_id' => [
                        DEPARTMENT_CATEGORY_MEDICAL_SPECIALTY,
                        DEPARTMENT_CATEGORY_CENTRAL_MEDICAL_FACILITY,
                        DEPARTMENT_CATEGORY_INTEGRATIVE_TREATMENT_DIAGNOSTICS,
                        DEPARTMENT_CATEGORY_MEDICAL_SUPPORT,
                        DEPARTMENT_CATEGORY_ADMIN_NURSING_CHIEF,
                    ]
                ]
            ]);

            $departments = [];
            foreach($results as $result){
                if( empty($result['schedule_url']) )
                    continue;
                $departments[] = $result;
            }
            $this->load->vars(compact('departments'));
        }

        // 特長
        if( $url['path'] === 'about/know_list' ){
            $this->load_model(['Feature']);
            $results = $this->Feature->search();
            $features = [];
            foreach($results as $result){
                $features[$result['category_id']][] = $result;
            }
            $this->load->vars(compact('features'));
        }

        // 本日の診療受付時間
        if ($url['path'] === '') {
            $reception_pattern = null;
            $this->load_model(['Option']);
            // 例外対応
            $exception_html = $this->Option->get_option('exception_html');
            if( !empty($exception_html)){
                $reception_pattern = 'exception_support';
                $this->load->vars(compact('exception_html'));
            }
            else {
                $reception_pattern = get_reception_pattern(NOW_DATE);
            }
            $this->load->vars(compact('reception_pattern'));
        }

        $dirname = dirname('/'. trim($url['path'],'/').'/index.php');
        $view_path = WWW_ROOT.$dirname.'/index.php';
        $this->load->view('page/display', compact('view_path'));
    }

}
