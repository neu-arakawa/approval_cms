<?php
class Api extends MY_Controller {

    // // 医師検索
    // public function doctors() {
    //     if (empty($_GET['name'])) {
    //         echo json_encode(null);
    //         exit();
    //     }
    //     $model = $this->_load_model('Doctor_model');
    //     $_GET['limit'] = 50;
    //     $data = $model->search(null, $pager, true, false);

    //     $doctors = [];
    //     if (!empty($data)) {
    //         foreach ($data as $d) {
    //             $doctors[] = $d['name'];
    //         }
    //     }
    //     echo json_encode([
    //         'data' => $doctors,
    //         'count' => $pager['count'],
    //     ]);
    // }

    // // 疾病検索
    // public function diseases () {
    //     if (empty($_GET['name'])) {
    //         echo json_encode(null);
    //         exit();
    //     }
    //     $model = $this->_load_model('Disease_model');
    //     $_GET['limit'] = 50;
    //     $data = $model->search(null, $pager, true, false);
    //     $diseases = [];
    //     if (!empty($data)) {
    //         foreach ($data as $d) {
    //             $diseases[] = $d['name'];
    //         }
    //     }
    //     echo json_encode([
    //         'data' => $diseases,
    //         'count' => $pager['count'],
    //     ]);
    // }

    // // 診療科の50音情報
    // public function dep_fc () {
    //     if (empty($_GET['type']) || !in_array($_GET['type'], [DEPARTMENT_TYPE_DEPARTMENT, DEPARTMENT_TYPE_SUPPORT])) {
    //         echo json_encode(null);
    //         exit();
    //     }
    //     if ($_GET['type']==DEPARTMENT_TYPE_DEPARTMENT) 
    //         $model = $this->_load_model('Department_model');
    //     else if ($_GET['type']==DEPARTMENT_TYPE_SUPPORT) 
    //         $model = $this->_load_model('Support_model');
    //     $data = $model->search(null, $pager, false, false);
    //     $match_arr = [
    //         'あいうえお', 'かきくけこがぎぐげご', 'さしすせそざじずぜぞ', 'たちつてとだぢづでどっ', 
    //         'なにぬねの', 'はひふへほばびぶべぼぱぴぷぺぽ',
    //         'まみむめも', 'やゆよゃゅょ', 'らりるれろ', 'わをん',
    //     ];

    //     $matched = [];
    //     if (!empty($data)) {
    //         foreach ($data as $d) {
    //             $kana = mb_substr($d['name_kana'], 0, 1);
    //             foreach ($match_arr as $m) {
    //                 if (mb_strpos($m, $kana)!==false) {
    //                     $fc = mb_substr($m, 0, 1);
    //                     if (!in_array($fc, $matched)) {
    //                         $matched[] = $fc;
    //                     }
    //                     break;
    //                 }
    //             }
    //         }
    //     }
    //     echo json_encode($matched);
    // }

    // // 診療科情報
    // public function departments () {
    //     if (empty($_GET['type']) || !in_array($_GET['type'], [DEPARTMENT_TYPE_DEPARTMENT, DEPARTMENT_TYPE_SUPPORT])) {
    //         echo json_encode(null);
    //         exit();
    //     }

    //     if ($_GET['type']==DEPARTMENT_TYPE_DEPARTMENT) 
    //         $model = $this->_load_model('Department_model');
    //     else if ($_GET['type']==DEPARTMENT_TYPE_SUPPORT) 
    //         $model = $this->_load_model('Support_model');
    //     $data = $model->search([
    //         'select' => 'name, name_kana, dir_name, featured_url, floor_url, place, tel_number, fax_number',
    //     ], $pager, false, false);

    //     echo json_encode($data);
    // }
}
