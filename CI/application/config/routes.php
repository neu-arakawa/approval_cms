<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'page/display';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route[ADMIN_DIR_NAME.'/login'] = 'admin/auth/login';
$route[ADMIN_DIR_NAME.'/logout'] = 'admin/auth/logout';
$route[ADMIN_DIR_NAME.'/request_reset'] = 'admin/auth/request_reset';
$route[ADMIN_DIR_NAME.'/reset/(:any)'] = 'admin/auth/reset/$1';
$route['preview'] = 'admin/page/preview';
$route[ADMIN_DIR_NAME.'/styles'] = 'admin/page/styles';


// 診察担当医表 担当医
$route[ADMIN_DIR_NAME.'/schedule_doctor'] = 'admin/schedule_doctor/replicate/1';
// $route[ADMIN_DIR_NAME.'/schedule_doctor/(:num)/replicate'] = 'admin/schedule_doctor/replicate/$1';
// $route[ADMIN_DIR_NAME.'/schedule_doctor/(:num)/delete_all/(:num)'] = 'admin/schedule_doctor/delete_doctors/$1/$2';
$route[ADMIN_DIR_NAME.'/schedule_doctor/(:num)'] = 'admin/schedule_doctor/timetable/$1';
$route[ADMIN_DIR_NAME.'/schedule_doctor/(:num)/(:num)'] = 'admin/schedule_doctor/timetable/$1/$2';
$route[ADMIN_DIR_NAME.'/schedule_doctor/(:num)/(:any)'] = 'admin/schedule_doctor/$2/$1';
$route[ADMIN_DIR_NAME.'/schedule_doctor/(:num)/(:any)/(:num)'] = 'admin/schedule_doctor/$2/$1/$3';

// // 担当医表　お知らせ
$route[ADMIN_DIR_NAME.'/schedule_info/(:num)/(:any)'] = 'admin/schedule_info/$2/$1';
$route[ADMIN_DIR_NAME.'/schedule_info/(:num)/(:any)/(:num)'] = 'admin/schedule_info/$2/$1/$3';

// 診療科固定ページ
$route[ADMIN_DIR_NAME.'/department_page/(:num)'] = 'admin/department_page/index/$1'; // 編集画面
$route[ADMIN_DIR_NAME.'/department_page/(:num)/(:any)'] = 'admin/department_page/$2/$1'; // 編集画面
$route[ADMIN_DIR_NAME.'/department_page/(:num)/(:any)/(:num)'] = 'admin/department_page/$2/$1/$3'; // 編集画面
$route[ADMIN_DIR_NAME.'/department_page/(:num)/(:any)/(:num)/(:num)'] = 'admin/department_page/$2/$1/$3/$4'; // 編集画面

$route[ADMIN_DIR_NAME.'/(:any)/(:any)/(:num)/(:any)'] = 'admin/$1/$2/$3/$4'; // 編集画面
$route[ADMIN_DIR_NAME.'/(:any)/(:any)/(:num)'] = 'admin/$1/$2/$3'; // 編集画面
$route[ADMIN_DIR_NAME.'/(:any)/(:any)'] = 'admin/$1/$2';
$route[ADMIN_DIR_NAME.'/(:any)'] = 'admin/$1';
$route[ADMIN_DIR_NAME] = 'admin/page';

// ファイルアップローダー
if (CMS_UPLOADER_ENABLED && CMS_UPLOADER_UPLOAD_DIR) {
    $route[CMS_UPLOADER_UPLOAD_DIR.'/(.+)'] = 'fileloader/loader/$1'; // CMS連携アップロード
}

// 診療科
$route['treatment/departments'] = 'department/index';
$route['treatment/departments/search_doctor'] = 'department/search_doctor';
$route['treatment/departments/(:any)'] = 'department/detail/$1';
$route['treatment/departments/(:any)/staff'] = 'department/detail/$1/staff';
$route['treatment/departments/(:any)/schedule'] = 'department/detail/$1/schedule';
$route['treatment/special_foreign'] = 'department/index';
$route['treatment/special_foreign/(:any)'] = 'department/detail/$1';
$route['treatment/special_foreign/(:any)/staff'] = 'department/detail/$1/staff';
$route['treatment/special_foreign/(:any)/schedule'] = 'department/detail/$1/schedule';
$route['treatment/kango_care'] = 'department/index';
$route['treatment/kango_care/(:any)'] = 'department/detail/$1';
$route['treatment/kango_care/(:any)/staff'] = 'department/detail/$1/staff';
$route['treatment/kango_care/(:any)/schedule'] = 'department/detail/$1/schedule';
$route['department/sidebar/(:any)'] = 'department/sidebar/$1';

// 固定ページ
$route['treatment/departments/(:any)/(.*)'] = 'department_page/page';
$route['treatment/special_foreign/(:any)/(.*)'] = 'department_page/page';
$route['treatment/kango_care/(:any)/(.*)'] = 'department_page/page';

// 臨床研究ページ
$route['about/research/(:any)'] = 'clinical_research/detail/$1';


// トピックス
$route['topics/detail/(:num)'] = 'topic/detail/$1';
$route['topics/(:num)'] = 'topic/topic_index/$1';
$route['topics'] = 'topic/topic_index';

// 新着情報
$route['news/detail/(:num)'] = 'news/detail/$1';
$route['news/preview_changes'] = 'news/preview_changes';
$route['news/(visitors|medical)'] = 'news/news_index/$1';
$route['news'] = 'news/news_index';
$route['about/lecture'] = 'news/about_lecture_index';

// レジメン
$route['medical/regimen/detail/(:num)'] = 'regimen/detail/$1';

// 特長
$route['about/know_list/detail/(:num)'] = 'feature/detail/$1';

// コンテンツ
$route['facility/related/kmu_towerhotel'] = 'content/page';

// 静的ページ
foreach(
    [
        'outpatient',
        'outpatient/first_time',
        'inpatient',
        'facility',
        'medical',
        'medical/patients_referral',
        'medical/regimen',
        'treatment',
        'treatment/second_opinion',
        'treatment/schedule',
        'about',
        'about/research',
        'about/know_list',
        'medical/faq',
    ]
    as $route_key
){
    $route[$route_key] = 'page/display';
}

$route['(:any)/(:any)/(:num)'] = '$1/$2/$3';
$route['(:any)/(:any)'] = '$1/$2';
$route['(:any)'] = '$1';
$route[''] = 'page/index';
