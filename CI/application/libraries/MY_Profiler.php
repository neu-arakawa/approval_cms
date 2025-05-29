<?php

// PHP8.2対応
#[AllowDynamicProperties]
class MY_Profiler extends CI_Profiler {
    public function __construct($config = array())
    {
        $this->_available_sections[] = 'view_vars';
        parent::__construct($config);
    }

    // プロファイラにユーザ変数を追加する
    protected function _compile_view_vars()
    {
        $output = "\n\n"
            .'<fieldset id="ci_profiler_viewvars" style="border:1px solid #cd6e00;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee;">'
            ."\n"
            .'<legend style="color:#cd6e00;">&nbsp;&nbsp;'.'View変数'."&nbsp;&nbsp;</legend>\n";

        $output .= '<pre>'.h(print_r($this->CI->load->get_my_vars(), true)).'</pre>';

        return $output.'</fieldset>';
    }

    public function run(){
        $profiler = parent::run();
        // return $profiler;
        $css = <<< 'EOM'
<style type="text/css">
#profiler_wrap * {
    margin: 0;
    padding: 0;
    border: 0;
    outline: 0;
    font-size: 15px;
    vertical-align: baseline;
    background: transparent;
    color: #000;
    line-height: 20px;
}
#profiler_wrap{
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index:10000;
    height: 0;
}
#profiler_inner{
    display: none;
}
#codeigniter_profiler{
    border: 1px solid #ddd;
    box-shadow:0px 4px 11px -2px rgba(0,0,0,.3);
}
#profiler_toolbar{
    list-style-type: none;
    text-align: right;
    background-color: rgba(255,255,255,1);
    opacity: .8;
    float:right;
    padding: 0;
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
    border: 1px solid #ddd;
    margin: 0;
    user-select: none;
}
#profiler_toolbar li{
    display: inline-block;
    margin: 0;
    padding: 3px 5px;
    border-right: 1px solid #ddd;
    cursor: pointer;
    color: #000;
    font-family: 'ヒラギノ角ゴ ProN', 'Hiragino Kaku Gothic ProN', 'メイリオ', Meiryo, 'ＭＳ Ｐゴシック', 'MS PGothic', sans-serif;
    font-size: 15px;
    vertical-align: middle;
}
#ci_profiler_session_data{
    display: block!important;
}
#ci_profiler_benchmarks,#ci_profiler_get,#ci_profiler_memory_usage,#ci_profiler_post,#ci_profiler_uri_string,
#ci_profiler_controller_info,#ci_profiler_queries,#ci_profiler_http_headers,#ci_profiler_csession,#ci_profiler_config, #ci_profiler_get{
    display:inline-block;
    width: 100%;
}
.profiler-btn img{
    height: 20px;
}
</style>
EOM;

        $script = <<< 'EOM'
<script>
    $(document).ready(function(){
        var prev_prof_ids = null;
        var $inner = $('#profiler_inner');
        var $btn = $('#profiler_toolbar .profiler-btn');
        var $tb_items = $('#profiler_toolbar li');
        var $profilers = $inner.find('fieldset');

        // DBのprofilerのみ、fieldsetではなく
        // その下の階層にIDが設定されるため、
        // その他の項目と合わせるためにfieldsetにIDを設定する
        $inner
            .find('[id^="ci_profiler_queries_db"]')
            .eq(0).closest('fieldset').attr('id','ci_profiler_queries');


        $profilers.hide();
        $btn.bind('click',function(){
            if($tb_items.filter('[data-ref]').eq(0).is(':visible')){
                $tb_items.filter('[data-ref]').hide();
                $inner.hide();
            }else{
                $tb_items.filter('[data-ref]').show();
                if(prev_prof_ids){
                    $inner.show();
                }
            }

        });
        $tb_items.filter('[data-ref]').hide();
        $inner.hide();

        $tb_items.filter('[data-ref]').bind('click',function(){
            $profilers.hide();
            var ids = $(this).attr('data-ref');
            if(ids){

                if(ids==prev_prof_ids && $inner.is(':visible')){
                    $inner.hide();
                    prev_prof_ids = null;
                }else{
                    $inner.show();
                    prev_prof_ids = ids;
                }

                arr = ids.split(',');
                for(var k in arr){
                    var id = arr[k];
                    $profilers.filter('#'+id).show();
                }

            }
        });
    });
</script>
EOM;

        $output = '<div id="profiler_wrap">';
        $output .= '<ul id="profiler_toolbar">';
        $output .= '<li><span class="profiler-btn"><img src="'.base_url('/admin/img/ci.svg').'" height="20"></span></li>';
        $output .= '<li data-ref="ci_profiler_benchmarks,ci_profiler_memory_usage,ci_profiler_config">Environment</li>';
        $output .= '<li data-ref="ci_profiler_get,ci_profiler_post,ci_profiler_uri_string,ci_profiler_http_headers">Request</li>';
        $output .= '<li data-ref="ci_profiler_queries">SQL</li>';
        $output .= '<li data-ref="ci_profiler_csession">Session</li>';
        $output .= '<li data-ref="ci_profiler_viewvars">ViewVars</li>';
        $output .= '</ul>';
        $output .= '<div id="profiler_inner">';
        $output .= $profiler;
        $output .= '</div>';
        $output .= $script;
        $output .= $css;
        $output .= '</div>';

        return $output;
    }
}
