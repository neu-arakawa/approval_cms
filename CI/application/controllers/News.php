<?php
class News extends MY_Controller
{
    public function news_index($selected_cateogry_id = null)
    {
        $options = [];
        if (!empty($selected_cateogry_id)) {
            $options['where_in']['category_id'] = $this->_model->convert_category_id($selected_cateogry_id);
        }
        $results = $this->_model->search($options, $pager, true);

        $this->load->view($this->_controller_name.'/index', compact('results', 'pager', 'selected_cateogry_id'));
    }


    public function about_lecture_index()
    {
        $options = [];
        $options['where']['category_id'] = NEWS_CATEGORY_TRAINING_SEMINAR;
        $options['limit'] = 5;
        $results = $this->_model->search($options, $pager, true);

        $this->load->view('about/lecture/index', compact('results', 'pager'));
    }


    public function detail($id=null){
        $data = parent::detail($id);
        if (!empty($data) && $data['link_type'] != NEWS_LINK_TYPE_CONTENT) {
            show_404();
        }
    }
}
